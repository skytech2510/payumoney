function checkUserSuspended(event) {
    if (userStatus == 'Suspended') {
        event.stopPropagation();
        window.location.href = userStatusCheckUrl;
        return false;
    }
}