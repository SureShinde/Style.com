/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    'Magento_Ui/js/modal/alert',
    'mage/template',
    "mage/calendar"
], function ($, alert, mageTemplate) {
    'use strict';
    $.widget('nearby.nearby', {
        options: {
        },
        _create: function () {
            var self = this;
            var map;
            var service;
            var infowindow;
            var pyrmont;
            var origin;
            var destination = "";
            var places = [];

            pyrmont = getCurrentLocation();

            $("body").on(
                "keypress",
                '.search-nearby-area [name="search-keyword"]',
                function (e) {
                    if (e.which == 13) {
                        var searchStr = $(this).val();
                        initMapTextSearch(pyrmont, searchStr);
                    }
                }
            );

            $("body").on(
                "click",
                '.nearby-places .place-detail span.remove',
                function () {
                    $(this).parents('.nearby-places').remove()
                }
            );

            function getCurrentLocation()
            {
                $.ajax(
                    {
                        url : self.options.google_map_api,
                        'method' : 'POST',
                        success: function (response) {
                            var latitude = response.results[0].geometry.location.lat;
                            var longitude = response.results[0].geometry.location.lng;
                            origin = response.results[0].place_id;
                            pyrmont = new google.maps.LatLng(latitude,longitude);
                        }
                    }
                );
            }

            function initMapTextSearch(pyrmont, searchStr)
            {

                map = new google.maps.Map(
                    document.getElementById('search-nearby-map'),
                    {
                        center: pyrmont,
                        zoom: 15
                    }
                );

                var request = {
                    location: pyrmont,
                    radius: '500',
                    query: searchStr
                };

                infowindow = new google.maps.InfoWindow();
                service = new google.maps.places.PlacesService(map);
                service.textSearch(
                    request,
                    function (results, status) {
                        if (status == google.maps.places.PlacesServiceStatus.OK) {
                            destination = "";
                            places = [];
                            for (var i = 0; i < results.length; i++) {
                                var place = results[i];
                                places[i] = place.name;
                                if (i==results.length-1) {
                                    destination += "place_id:"+place.place_id;
                                } else {
                                    destination += "place_id:"+place.place_id + "|";
                                }
                                createMarker(place);
                            }
                            getDistanceAndTime(destination, places);
                        }
                    }
                );
            }

            function initMapNearBy(pyrmont)
            {
                map = new google.maps.Map(
                    document.getElementById('search-nearby-map'),
                    {
                        center: pyrmont,
                        zoom: 15
                    }
                );

                var request = {
                    location: pyrmont,
                    radius: '500',
                    type: ['restaurant']
                };

                service = new google.maps.places.PlacesService(map);
                service.nearbySearch(
                    request,
                    function (results, status) {
                        if (status == google.maps.places.PlacesServiceStatus.OK) {
                            for (var i = 0; i < results.length; i++) {
                                var place = results[i];
                                createMarker(results[i]);
                            }
                        }
                    }
                );
            }

            function createMarker(place)
            {
                var placeLoc = place.geometry.location;
                var marker = new google.maps.Marker(
                    {
                        map: map,
                        position: place.geometry.location
                    }
                );

                google.maps.event.addListener(
                    marker,
                    'click',
                    function () {
                        infowindow.setContent(place.name);
                        infowindow.open(map, this);
                    }
                );
            }

            function getDistanceAndTime(destination, places)
            {
                $.ajax({
                    url : self.options.distance_url,
                    method : 'POST',
                    data : {
                        origins : "place_id:"+origin,
                        destinations : destination,
                        key : self.options.google_api_key,
                        places: places
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.length) {
                            $('.search-nearby-result').html("");
                            $.each(
                                response,
                                function (idx, val) {
                                    var progressTmpl = mageTemplate('#search-nearby-result-template'),
                                    tmpl;
                                    tmpl = progressTmpl(
                                        {
                                            data: {
                                                name: val.name,
                                                distance: val.distance+"("+val.duration+")"
                                            }
                                        }
                                    );
                                    $('.search-nearby-result').append(tmpl);
                                }
                            );
                        }
                    },
                    error: function (response) {
                    }
                });
            }
        }
    });
    return $.nearby.nearby;
});
