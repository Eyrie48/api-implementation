<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('/authorization', function (Request $request) {  //Get authorization
    $request->session()->put('state', $state = Str::random(40));
 
    $query = http_build_query([
        'client_id' => '8',
        'redirect_uri' => 'http://localhost:8000/callback',
        'response_type' => 'code',
        'scope' => '',
        'state' => $state,
    ]);

    return redirect('http://localhost:8000/oauth/authorize?'.$query);
})->name('authorization');

Route::get('/callback', function (Request $request) {   //Get Token after authorization
    $state = $request->session()->pull('state');
    
    if(strlen($state) > 0 && $state === $request->state) {
 
        $response = Http::asForm()->post('http://localhost:8001/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => '8',
            'client_secret' => 'VP2J5V4C1FK1ndIkaQ38HWm5PcqdVzrAmmUtQnX6',
            'redirect_uri' => 'http://localhost:8000/callback',
            'code' => $request->code,
        ]);
        
        $accessToken = $response->json()['access_token'];
    
        //Use the token to request data
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$accessToken,
        ])->get('http://localhost:8001/api/users');
        
        return $response->json();
        
    } else {
        return redirect()->route('authorization');
    }
});



require __DIR__.'/auth.php';
