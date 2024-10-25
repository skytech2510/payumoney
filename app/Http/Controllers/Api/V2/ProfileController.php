<?php

/**
 * @package ProfileController
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 05-12-2022
 */

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Exceptions\Api\V2\{
    UserProfileException,
    LoginException,
    WalletException
};
use App\Http\Requests\{
    CheckUserDuplicatePhoneNumberRequest,
    UpdatePasswordRequest,
    UploadUserProfilePictureRequest
};
use App\Services\{
    UserProfileService,
    WalletService
};
use App\Models\{
    Wallet,
    User
};
use Exception, DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserProfileResource;

/**
 * @group  User Profile
 *
 * API to manage user profile
 */
class ProfileController extends Controller
{
    /**
     * Show User Profile summary
     *
     * @param UserProfileService $service
     * @return JsonResponse
     * @throws LoginException
     */
    public function summary(UserProfileService $service)
    {
        try {
            $user = $service->getProfileSummary(auth()->id());
            return $this->successResponse(new UserProfileResource($user));
        } catch (LoginException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * Show User Profile details
     *
     * @param UserProfileService $service
     * @return JsonResponse
     * @throws LoginException
     */
    public function details(UserProfileService $service)
    {
        try {
            $userDetails = $service->getProfileDetails(auth()->id());
            return $this->successResponse(new UserProfileResource($userDetails));
        } catch (LoginException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * Update User Profile informatpion
     *
     * @param Request $request
     * @param UserProfileService $service
     * @return JsonResponse
     * @throws Exception
     */
    public function update(Request $request, UserProfileService $service)
    {
        try {
            DB::beginTransaction();
            $userId = auth()->id();
            $userInfo = $request->only('first_name', 'last_name');
            $userDetailInfo = $request->only('country_id', 'address_1', 'address_2', 'city', 'state', 'timezone');

            if (!empty($request->defaultCountry) && !empty($request->carrierCode)) {
                $userPhoneInfo = $request->only('phone', 'defaultCountry', 'carrierCode');
                $service->phoneUpdate($userId, $userPhoneInfo);
            }

            $service->updateProfileInformation($userId, $userInfo, $userDetailInfo);
            $defaultWallet = $request->default_wallet;
            (new WalletService())->changeDefaultWallet($userId, $defaultWallet);
            DB::commit();
            return $this->okResponse();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->unprocessableResponse([], __($e->getMessage()));
        }
    }

    /**
     * Change User Profile Picture
     *
     * @param UploadUserProfilePictureRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function uploadImage(UploadUserProfilePictureRequest $request, UserProfileService $service)
    {
        try {
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $response = $service->uploadImage(auth()->id(), $image);
            }
            if (true === $response['status']) {
                return $this->okResponse([], $response['message']);
            }
            return $this->unprocessableResponse([], $response['message']);
        } catch (UserProfileException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * Change User Password
     *
     * @param UpdatePasswordRequest $request
     * @param UserProfileService $service
     * @return JsonResponse
     * @throws UserProfileException
     */
    public function changePassword(UpdatePasswordRequest $request, UserProfileService $service)
    {
        try {
            $oldPassword = $request->old_password;
            $password = $request->password;
            $response = $service->changePassword(auth()->id(), $oldPassword, $password);
            return $this->okResponse([], $response['message']);
        } catch (UserProfileException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * Get default Wallet balance
     *
     * @param WalletService $service
     * @throws WalletException
     * @return JsonResponse
     */
    public function getDefaultWalletBalance(WalletService $service)
    {
        try {
            return $this->okResponse($service->defaultWalletBalance(auth()->id()));
        } catch (WalletException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * Get user's all available wallet balances
     *
     * @return JsonResponse
     * @throws WalletException
     */
    public function getUserAvailableWalletsBalance()
    {
        try {
            $wallet = new Wallet();
            $wallets = $wallet->getAvailableBalance(auth()->id());
            if (!$wallets) {
                throw new WalletException(__("No :X found.", ["X" => __("Wallet")]));
            }
            return $this->okResponse($wallets);
        } catch (WalletException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * Check current user's status
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkUserStatus()
    {
        return $this->okResponse(['status' => User::where(['id' => auth()->id()])->value('status')]);
    }

    /**
     * Check Duplicate phone number when updating phone
     *
     *
     * @return JsonResponse
     */
    public function checkDuplicatePhoneNumber(CheckUserDuplicatePhoneNumberRequest $request)
    {
        return $this->successResponse([
            'status' => true,
            'success' => __("The phone number is Available!")
        ]);
    }


}
