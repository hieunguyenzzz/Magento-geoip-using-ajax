define([
        "jquery"
    ],
    function ($) {
        "use strict";
        return function (config) {
            if (config.url) {
                let geoIpCheck = new Promise(function (resolve, reject) {
                    $.ajax({
                        type: "POST",
                        url: config.url,
                        dataType: "json",
                        contentType: 'application/json',
                        data: JSON.stringify(config),
                        success: data => {
                            let response = JSON.parse(data);
                            resolve(response);
                        }
                    })

                });

                geoIpCheck.then(data => {
                    let {url} = data;
                    if (typeof url !== 'undefined') {
                        console.log(url);
                        window.location.href = url;
                    }
                });
            }
        }
    });