function isValidPhoneNumber(phone)
{
    var filter = /^[\d-]*$/; //only allow digits and hyphens - custom regex
    if (filter.test(phone))
    {
        return true;
    }
    else
    {
        return false;
    }
}