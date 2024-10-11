<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use App\Models\Question;
use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\QuestionnaireSentMail;
use App\Mail\QuestionnaireCompletedMail;
use App\Models\ClientProfile; 
use Illuminate\Support\Facades\Auth;

class QuestionnaireController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    // Afficher un formulaire pour créer un nouveau questionnaire
    public function create()
    {
        return view('questionnaires.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'required|array',
        ]);

        // Create the questionnaire
        $questionnaire = Questionnaire::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => Auth::id(), // Associate with the logged-in therapist
        ]);

        // Loop through the questions and create them
        foreach ($request->questions as $question) {
            $newQuestion = new Question();
            $newQuestion->questionnaire_id = $questionnaire->id;
            $newQuestion->text = $question['text'];
            $newQuestion->type = $question['type'];
            
            // If it's multiple choice, store the options as a comma-separated string
            if ($question['type'] === 'multiple_choice' && isset($question['options'])) {
                $newQuestion->options = $question['options'];
            }

            $newQuestion->save();
        }

        return redirect()->route('questionnaires.index')->with('success', 'Questionnaire créé avec succès.');
    }

    // Afficher la liste des questionnaires
    public function index()
    {
        $questionnaires = Questionnaire::where('user_id', Auth::id())->get(); // Fetch only the logged-in user's questionnaires
        return view('questionnaires.index', compact('questionnaires'));
    }

    // Envoyer le questionnaire à un client
    public function send(Request $request)
    {
        $request->validate([
            'client_profile_id' => 'required|exists:client_profiles,id',
            'questionnaire_id' => 'required|exists:questionnaires,id',
            'action' => 'required|in:fill_now,send_email',
        ]);

        $clientProfile = ClientProfile::findOrFail($request->client_profile_id);
        $questionnaire = Questionnaire::findOrFail($request->questionnaire_id);

        // Check authorization
        $this->authorize('view', $questionnaire); // Ensure the therapist owns the questionnaire

        if ($request->action === 'fill_now') {
            // Generate a token and redirect to the fill page
            $token = Str::random(32);
            Response::create([
                'questionnaire_id' => $questionnaire->id,
                'client_profile_id' => $clientProfile->id,
                'token' => $token,
                'answers' => json_encode([]), // Initialize answers as an empty array
            ]);
            return redirect()->route('questionnaires.fill', ['token' => $token]);
        } else {
            // Send the email
            $token = Str::random(32);
            Response::create([
                'questionnaire_id' => $questionnaire->id,
                'client_profile_id' => $clientProfile->id,
                'token' => $token,
                'answers' => json_encode([]),
            ]);
            $client_profile_name = $clientProfile->first_name;
            $questionnaire_name = $questionnaire->title;
            $therapist = Auth::user();
            $therapistName = $therapist->name;
            $link = route('questionnaires.fill', ['token' => $token]);
            Mail::to($clientProfile->email)->send(new QuestionnaireSentMail($therapistName, $questionnaire_name, $link, $client_profile_name));
            return redirect()->route('client_profiles.show', $clientProfile->id)->with('success', 'Questionnaire envoyé avec succès.');
        }
    }

    public function fill($token)
    {
        // Find the response based on the token
        $response = Response::where('token', $token)->firstOrFail();

        // Get the associated questionnaire and its questions
        $questionnaire = $response->questionnaire; 
        $questions = $questionnaire->questions;

        return view('questionnaires.fill', compact('token', 'questionnaire', 'questions'));
    }

public function storeResponses(Request $request, $token)
{
    // Find the response based on the token
    $response = Response::with('questionnaire.user')->where('token', $token)->firstOrFail(); 

    $request->validate([
        'answers' => 'required|array',
    ]);

    // Save the answers
    $response->answers = json_encode($request->answers);
    $response->is_completed = true;
    $response->save();

    // Check if the user is authenticated
    if (auth()->check()) {
        // Send email notification if the user exists
        if ($response->questionnaire->user) {
            Mail::to($response->questionnaire->user->email)->queue(new QuestionnaireCompletedMail($response));
        } else {
            // Log a warning if the user does not exist
            Log::warning('User not found for questionnaire ID: ' . $response->questionnaire->id);
        }

        return redirect()->route('questionnaires.index')->with('success', 'Questionnaire soumis avec succès.');
    } else {
        // Redirect to a thank you page for unauthenticated users
        return redirect()->route('thank_you'); // Make sure to define this route in your routes file
    }
}

    public function showResponse($id)
    {
        // Fetch the response along with the associated questionnaire and client profile
        $response = Response::with(['questionnaire', 'clientProfile'])->findOrFail($id);
        
        // Check if the authenticated user is allowed to view this client profile
        if ($response->questionnaire->user_id !== auth()->id()) {
            abort(403, __('Unauthorized action.'));
        }

        // Return the view with the response data
        return view('questionnaires.show_response', compact('response'));
    }

    // Supprimer un questionnaire
    public function destroy(Questionnaire $questionnaire)
    {
        $this->authorize('delete', $questionnaire);
        $questionnaire->delete();

        return redirect()->route('questionnaires.index')->with('success', 'Questionnaire supprimé avec succès.');
    }

    public function showSendQuestionnaire()
    {
        $questionnaires = Questionnaire::where('user_id', Auth::id())->get(); // Fetch questionnaires for the therapist
        $clients = ClientProfile::where('user_id', Auth::id())->get(); // Fetch clients for the therapist

        return view('questionnaires.send', compact('questionnaires', 'clients'));
    }

    public function show($id)
    {
        // Fetch the questionnaire along with its questions
        $questionnaire = Questionnaire::with('questions')->findOrFail($id);
        
        // Check authorization
        $this->authorize('view', $questionnaire); // Ensure the therapist owns the questionnaire

        return view('questionnaires.show', compact('questionnaire'));
    }
}
