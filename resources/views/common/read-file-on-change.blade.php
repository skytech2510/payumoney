<script type="text/javascript">
    function readFileOnChange(element,previewElement,orginalSource)
    {
        var file, reader;
        if (file = element.files[0])
        {
            reader = new FileReader();
            reader.onload = function()
            {
                if (file.name.match(/.(png|jpg|jpeg|gif|bmp)$/i))
                {
                    previewElement.attr({src: reader.result});
                }
                else
                {
                    previewElement.attr({src: orginalSource});
                }
            }
            reader.readAsDataURL(file);
        }
    }
</script>