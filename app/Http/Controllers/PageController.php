<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
public function index(){
    return view("welcome");
}

public function uploadpage(){
    return view("product");
}

public function store(Request $request){

    $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:mp4,mov,avi|max:102400',
            'name' => 'nullable|string|max:255', 
    ]);

    try {
    $file = $request->file('file');
    

    $video = new Video();
    $video->user_id = 1;
    
    $video->name = $request->name;
    $video->title=$request->title;
    $video->description= $request->description;
    $filename = $video->name;
    
    $path = $file->storeAs('public', $filename);
    $video->file = $filename;

    $video->path = $path;
    $video->url = Storage::url($path);

    $video->save();


    return response()->json([
        'message' => 'Video uploaded successfully',
        'url' => $video->url
    ]);


    // return redirect()->back();

}
catch (\Exception $e) {
    return response()->json([
        'message' => 'File upload failed',
        'error' => $e->getMessage()
    ], 500);
}

}
}