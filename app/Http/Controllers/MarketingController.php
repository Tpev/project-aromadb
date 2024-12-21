<?php
namespace App\Http\Controllers;

use App\Models\MarketingEmail;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    // Show the upload form
    public function showUploadForm()
    {
		    // Check if the user is an admin
    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }
	
        return view('admin.marketing.upload-csv');
    }

    // Handle the CSV upload
    public function uploadCsv(Request $request)
    {
		    // Check if the user is an admin
    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:4048',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        // Read CSV file
        $fileHandle = fopen($path, 'r');
        $header = fgetcsv($fileHandle);

        if (!$header || !in_array('email', $header) || !in_array('firstname', $header) || !in_array('lastname', $header)) {
            return back()->withErrors(['error' => 'Invalid CSV format. Required columns: firstname, lastname, email.']);
        }

        $header = array_map('strtolower', $header);

        while (($data = fgetcsv($fileHandle)) !== false) {
            $row = array_combine($header, $data);

            // Skip if email already exists
            if (MarketingEmail::where('email', $row['email'])->exists()) {
                continue;
            }

            // Insert into database
            MarketingEmail::create([
                'firstname' => $row['firstname'],
                'lastname' => $row['lastname'],
                'email' => $row['email'],
                'tags' => $row['tags'] ?? null,
            ]);
        }

        fclose($fileHandle);

        return redirect()->route('admin.marketing.emails')->with('success', 'CSV uploaded successfully.');
    }

    // Display the list of emails
    public function viewEmails()
    {
		    // Check if the user is an admin
    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }
        $emails = MarketingEmail::all();
        return view('admin.marketing.emails', compact('emails'));
    }
}
