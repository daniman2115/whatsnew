<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;

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


    $video = new Video();

    $file=$request->file;
    $filename= time().".".$file->getClientOriginalExtension();
    $request->file->move("assets", $filename);
    $video->file=$filename;



    $video->user_id = 1;

    // $data->user_id=User::where("id",$data->user_id)->first()->id;
    $video->name = $request->name;
    $video->title=$request->title;
    $video->description= $request->description;

    $video->save();


    
    return redirect()->back();

}

}