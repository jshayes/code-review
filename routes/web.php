<?php

use App\User;
use Carbon\Carbon;
use Github\Client;
use Github\ResultPager;
use App\Notifications\CodeReview;
use Illuminate\Support\Collection;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
