<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
class ImportController extends Controller
{
    public function import (Request $request)
    {
    	$request->validate([
    		'file' => 'required|mimes:csv,xlsx'
    	]);

    	$extension = $request->file->getClientOriginalExtension();
    	$file = file($request->file->getRealPath());
    	$data = array_slice($file, 1);

    	$parts =  (array_chunk($data, 5000));
    	foreach ($parts as $key => $part) {
    		$filename = storage_path('pending-files/'.date('y-m-d-H-i-s').$key.'.csv'/*.$extension*/);
    		file_put_contents($filename, $part);
    	}

    	$this->importToDb($extension);
    	return response(['status'=>'successfuly imported']);
    }

    public function importToDb($extension)
    {
    	$path = storage_path('pending-files/*'.'.'.'.csv'/*.$extension*/);

    	$allFilesPaths = glob($path);

    	foreach (array_slice($allFilesPaths, 0, 1) as $file) {
    		$data = array_map('str_getcsv', file($file));
    		
    		foreach ($data as $row) {
    			User::create([
    				'first_name' => $row[0],
    				'second_name' => $row[1],
    				'family_name' => $row[2],
    				'UID' => $row[3],
    			]);
    		}

    		unlink($file);
    	}
    }
}
