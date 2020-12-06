<?php

namespace App\Http\Controllers\Api;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Test;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Mews\Purifier\Facades\Purifier;

class HiringOrganizationTestController extends Controller
{
    public function index(Request $request){

        $tests = $request->user()->role->company->tests()->withCount(['questions', 'requirements'])->get();

        return response([
            'tests' => $tests
        ]);
    }

    public function show(Request $request, Test $test){
        if (!$this->belongsToOrg($request->user(), $test)){
            return response([
                'message' => 'Not authorized'
            ], 403);
        }

        $test->load('questions');

        return response([
            'test' => $test
        ]);
    }

    public function store(Request $request){
        $this->validate($request, [
            'name' => 'required|string',
            'max_tries' => 'required|numeric|min:1',
            'min_passing_criteria' => 'required|between:1,100',
            'html' => 'required|string',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.reference' => 'url',
            'questions.*.option_1' => 'required|string',
            'question.*.option_2' => 'required|string',
            'question.*.option_3' => 'string',
            'question.*.option_4' => 'string',
            'question.*.correct_answer' => 'required|string'
        ]);

        if ($request->user()->role->company->tests()->where('name', $request->get('name'))->exists()){
            return response([
                'errors' => [
                    'name' => [
                        __('validation.unique', ['attribute' => 'name'])
                    ]
                ]
            ], 418);
        }

        $request->merge([
            'html' => Purifier::clean($request->get('html'))
        ]);

        DB::beginTransaction();

        $test = $request->user()->role->company->tests()->create([
            'name' => $request->get('name'),
            'max_tries' => $request->get('max_tries'),
            'min_passing_criteria' => $request->get('min_passing_criteria'),
            'html' => $request->get('html')
        ]);

        foreach($request->get('questions') as $question){

            if (isset($question['question_text'])){
                $question['question_text'] = Purifier::clean($question['question_text']);
            }

            $question = $test->questions()->create($question);

            if (!in_array($question['correct_answer'], $question->options, true)){

                DB::rollBack();

                return response([
                    'errors' => [
                        'questions' => 'The correct answer does not match a valid option' //TODO translate
                    ]
                ], 418);

            }

        }

        DB::commit();

        $test->load('questions');

        return response(['test' => $test]);

    }

    public function update(Request $request, Test $test){

        if (!$this->belongsToOrg($request->user(), $test)){
            return response([
                'message' => 'Not authorized'
            ], 403);
        }

        $this->validate($request, [
            'name' => 'string',
            'max_tries' => 'numeric',
            'min_passing_criteria' => 'between:1,100',
            'html' => 'string'
        ]);

        if ($request->user()->role->company->tests()->where('name', $request->get('name'))->where('id', '!=', $test->id)->exists()){
            return response([
                'errors' => [
                    'name' => [
                        __('validation.unique', ['attribute' => 'name'])
                    ]
                ]
            ], 418);
        }

        if ($request->has('html')){
            $request->merge([
                'html' => Purifier::clean($request->get('html'))
            ]);
        }

        $test->update($request->all());

        return response(['test' => $test]);

    }

    public function updateQuestion(Request $request, Test $test, Question $question){
        if (!$this->belongsToOrg($request->user(), $test) || $question->test_id !== $test->id){
            return response([
                'message' => 'Not authorized'
            ], 403);
        }

        $this->validate($request, [
            'question_text' => 'string',
            'reference' => 'url',
            'option_1' => 'string',
            'option_2' => 'string',
            'option_3' => 'string',
            'option_4' => 'string',
            'correct_answer' => 'string'
        ]);

        if ($request->has('question_text')){
            $request->merge([
                'question_text' => Purifier::clean($request->get('question_text'))
            ]);
        }

        DB::beginTransaction();

        $question->update($request->all());

        if (!in_array($question->correct_answer, $question->options, true)){
            DB::rollBack();
            return response([
                'errors' => [
                    'questions' => 'The correct answer does not match a valid option' //TODO validate
                ]
            ], 418);
        }

        DB::commit();

        $test->load('questions');

        return response(['test' => $test]);

    }

    public function destroyQuestion(Request $request, Test $test, Question $question){

        if (!$this->belongsToOrg($request->user(), $test) || $question->test_id !== $test->id){
            return response([
                'message' => 'Not authorized'
            ], 403);
        }

        $question->delete();

        return response(['message' => 'ok']);

    }

    public function storeQuestion(Request $request, Test $test)
    {
        if (!$this->belongsToOrg($request->user(), $test)){
            return response([
                'message' => 'Not authorized'
            ], 403);
        }

        $this->validate($request, [
            'question_text' => 'required|string',
            'reference' => 'url',
            'option_1' => 'required|string',
            'option_2' => 'required|string',
            'option_3' => 'string',
            'option_4' => 'string',
            'correct_answer' => 'required|string'
        ]);

        $request->merge([
            'question_text' => Purifier::clean($request->get('question_text'))
        ]);

        DB::beginTransaction();

        $question = $test->questions()->create($request->all());

        if (!in_array($request->get('correct_answer'), $question->options, true)){
            DB::rollBack();
            return response([
                'errors' => [
                    'questions' => 'The correct answer does not match a valid option' //TODO validate
                ]
            ], 418);
        }

        DB::commit();

        $test->load('questions');

        return response($test);

    }

    public function destroy(Request $request, Test $test){
        if (!$this->belongsToOrg($request->user(), $test)){
            return response([
                'message' => 'Not authorized'
            ], 403);
        }

        $test->delete();

        return response([
            'message' => 'ok'
        ]);
    }

    private function belongsToOrg($user, $test){
        return $user->role->entity_id === $test->hiring_organization_id;
    }

}
