<?php

Route::get('l.js', "AdsController@adsAction");

Route::get('url', "ClickController@clickAction");

Route::get('csstype.css', "CsstypeController@cssAction");

Route::get('se', "PvController@pvAction");

Route::get('{pos}/{pid}', "AdsController@adsAction");