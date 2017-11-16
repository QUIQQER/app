if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(
        URL_OPT_DIR + 'quiqqer/app/bin/service-worker.php',
        {scope: '/'}
    ).then(function ()
    {
        console.log('service worker installed')
    }).catch(function (err)
    {
        console.log('Error', err)
    });
}