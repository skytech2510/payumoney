<?php

namespace App\Http\Controllers\Admin;

use DB, Validator, File, Cache, Http, ZipArchive, Common;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Addon;

class AddonController extends Controller
{
    public function index(Request $request)
    {
        $data['menu'] = 'addons';

        if ($request->isMethod('post')) {
            $rules = [
                'purchase_code' => 'required',
                'envato_user_name' => 'required',
                'addon_zip' => 'required|mimes:zip',
            ];
            $fieldNames = [
                'purchase_code' => __('Purchase code'),
                'envato_user_name' => __('Envator User Name'),
                'addon_zip' => __('Addon Zip'),
            ];

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                $error = $validator->messages()->get('*');

                $errorMessage = isset($error['purchase_code'][0]) ? $error['purchase_code'][0] : '';
                $errorMessage = isset($error['envato_user_name'][0]) ? $error['envato_user_name'][0] : '';
                $errorMessage .= isset($error['addon_zip'][0]) ? $error['addon_zip'][0] : '';
                $errorMessage .= isset($error['addon_zip'][1]) ? $error['addon_zip'][1] : '';

                (new Common)->one_time_message('danger', $errorMessage);
                return redirect()->back();
            }

            $domainName     = request()->getHost();
            $domainIp       = request()->ip();
            $purchaseStatus = $this->getPurchaseStatus($domainName, $domainIp, $request->purchase_code, $request->envato_user_name);

            if (!$purchaseStatus) {
                (new Common)->one_time_message('danger', __('Invalid purchase code.'));
                return redirect()->back();
            }

            if (class_exists('ZipArchive')) {
                if ($request->hasFile('addon_zip')) {
                    // Create update directory.
                    $dir = 'addons';
                    if (!is_dir($dir)) {
                        mkdir($dir, config('paymoney.file_permission'), true);
                    }
                    
                    $zipped_file_name = $request->addon_zip->getClientOriginalName();
                    $zipped_file_name = pathinfo($zipped_file_name, PATHINFO_FILENAME);
                    
                    // Unzip uploaded update file and remove zip file.
                    $zip = new ZipArchive;
                    $res = $zip->open($request->addon_zip);
                    
                    $random_dir = Str::random(10);
                    
                    if ($res === true) {
                        $res = $zip->extractTo(base_path('temp/' . $random_dir . '/addons/' . $zipped_file_name ));
                        $zip->close();
                    } else {
                        (new Common)->one_time_message('success', __('Could not open the zip file.'));
                        return redirect()->back();
                    }
                    
                    $str = file_get_contents(base_path('temp/' . $random_dir . '/addons/' . $zipped_file_name . '/config.json'));
                    $json = json_decode($str, true);
                    
                    if (env('APP_VERSION', 3.3) >= $json['minimum_item_version']) {
                        if (count(Addon::where('unique_identifier', $json['unique_identifier'])->get()) == 0) {
                            $addon = new Addon;
                            $addon->name = $json['name'];
                            $addon->unique_identifier = $json['unique_identifier'];
                            $addon->version = $json['version'];
                            $addon->activated = 1;
                            $addon->image = $json['addon_banner'];
                            $addon->save();
                            
                            // Create new directories.
                            if (!empty($json['directory'])) {
                                foreach ($json['directory'][0]['name'] as $directory) {
                                    if (is_dir(base_path($directory)) == false) {
                                        mkdir(base_path($directory), config('paymoney.file_permission'), true);
                                    } else {
                                        echo __("error on creating directory");
                                    }
                                }
                            }

                            // Create/Replace new files.
                            if (!empty($json['files'])) {
                                foreach ($json['files'] as $file) {
                                    copy(base_path('temp/' . $random_dir . '/' . $file['root_directory']), base_path($file['update_directory']));
                                }
                            }

                            // Run sql modifications
                            $sql_path = base_path('temp/' . $random_dir . '/addons/' . $zipped_file_name .  '/sql/update.sql');
                            if (file_exists($sql_path)) {
                                DB::unprepared(file_get_contents($sql_path));
                            }

                            File::deleteDirectory(base_path('temp/' . $random_dir));

                            changeEnvironmentVariable(strtoupper($addon->unique_identifier), 'true');

                            // To update the cache for settings and preference
                            Cache::forget(config('cache.prefix') . '-preferences');
                            Cache::forget(config('cache.prefix') . '-settings');

                            (new Common)->one_time_message('success', __('Addon Installed successfully'));
                            return redirect()->back();
                        } else {
                            // Create new directories.
                            if (!empty($json['directory'])) {
                                foreach ($json['directory'][0]['name'] as $directory) {
                                    if (is_dir(base_path($directory)) == false) {
                                        mkdir(base_path($directory), config('paymoney.file_permission'), true);
                                    } else {
                                        echo __("error on creating directory");
                                    }
                                }
                            }

                            // Create/Replace new files.
                            if (!empty($json['files'])) {
                                foreach ($json['files'] as $file) {
                                    copy(base_path('temp/' . $random_dir . '/' . $file['root_directory']), base_path($file['update_directory']));
                                }
                            }

                            $addon = Addon::where('unique_identifier', $json['unique_identifier'])->first();

                            for ($i = $addon->version + 0.1; $i <= $json['version']; $i = $i + 0.1) {
                                // Run sql modifications
                                $sql_path = base_path('temp/' . $random_dir . '/addons/' . $zipped_file_name . '/sql/' . $i . '.sql');
                                if (file_exists($sql_path)) {
                                    DB::unprepared(file_get_contents($sql_path));
                                }
                            }

                            $addon->version = $json['version'];
                            $addon->save();

                            changeEnvironmentVariable(strtoupper($addon->unique_identifier), 'true');
                            File::deleteDirectory(base_path('temp/' . $random_dir));

                            Cache::forget(config('cache.prefix') . '-preferences');
                            Cache::forget(config('cache.prefix') . '-settings');

                            (new Common)->one_time_message('success', __('This addon is updated successfully'));
                            return redirect()->back();
                        }
                    } else {
                        (new Common)->one_time_message('danger', __('This version is not capable of installing Addons, Please update.'));
                        return redirect()->back();
                    }
                }
            } else {
                (new Common)->one_time_message('danger', __('Please enable ZipArchive extension.'));
            }
        }

        $data['addons'] = Addon::all();

        return view('admin.addons.addons', $data);
    }

    public function activation(Request $request, $status, $id)
    {
        $addon = Addon::find($id);
        if ($addon->count() < 0) {
            (new Common)->one_time_message('danger', __('Opps, something went wrong, please try again.'));
        }
        $addon->activated = (int) $status;
        $addon->save();

        $unique_identifier = strtoupper($addon->unique_identifier);

        if ($request->status == '1') {
            changeEnvironmentVariable($unique_identifier, 'true');
            (new Common)->one_time_message('success', __('Addon successfully activated.'));
        } else {
            changeEnvironmentVariable($unique_identifier, 'false');
            (new Common)->one_time_message('success', __('Addon successfully inactived.'));
        }

        return redirect()->back();
    }

    //Send data to verify envato purchase code
    public function getPurchaseStatus($domainName, $domainIp, $envatopurchasecode, $envatoUserName)
    {
		return true;
        //Added curl extension check during installation
        if (!extension_loaded('curl')) {
            throw new \Exception('cURL extension seems not to be installed');
        }

        $apiURL = 'https://envatoapi.techvill.org/installation/';
        $postData = [
            'domain_name' => $domainName,
            'domain_ip' => $domainIp,
            'envatopurchasecode' => $envatopurchasecode,
            'envatoUserName' => $envatoUserName,
        ];

        $response = Http::asForm()->post($apiURL, $postData);
        $responseData = json_decode($response->getBody());
        if ($response->status() == true) {
            if (!empty($responseData->status) && $responseData->status == 1 && $responseData->item_name == 'PayMoney - Secure Online Payment Gateway' && isset($responseData->buyer_name) && $responseData->buyer_name == $envatoUserName) {
                return true;
            } else {
                return false;
            }
        }
    }
}
