<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ReassignTaskRequest;
use App\Http\Requests\Tasks\ApproveTaskRequest;
use App\Http\Requests\Tasks\CompleteTaskRequest;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use App\Models\Task;
use App\Models\TaskType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{

    /**
     * Get tasks assigned to user and created by user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        return response()->json([
            "assigned_tasks" => $request->user()->assignedTasks()->with(['assigner.highestRole', 'attachments'])->get(),
            "created_tasks" => $request->user()->createdTasks()->with(['assignee.highestRole', 'attachments'])->get()
        ]);

    }

    public function show(Request $request, Task $task){

        if ($request->user()->id !== $task->assigned_to && $request->user()->id !== $task->assigned_by){
            return response('not authorized', 403);
        }

        return response()->json([
            "task" => $task->load(['assignee', 'assigner', 'attachments'])
        ]);
    }

    /**
     * Get tasks assigned to user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignedTasks(Request $request)
    {

        return response()->json([
            "assigned_tasks" => $request->user()->assignedTasks()->with(['assigner.highestRole', 'attachments'])->get()
        ]);

    }

    /**
     * Get tasks created by user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createdTasks(Request $request)
    {

        return response()->json([
            "created_tasks" => $request->user()->createdTasks()->with(['assignee.highestRole', 'attachments'])->get()
        ]);

    }

    /**
     * Create new tasks
     *
     * //TODO validate that user is contactable
     * @param StoreTaskRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTaskRequest $request)
    {

        $task = Task::create([
            'assigned_by' => $request->user()->id,
            'assigned_to' => $request->get('assigned_to'),
            'short_description' => $request->get('short_description'),
            'long_description' => $request->get('long_description'),
            'task_type_id' => $request->get('task_type_id'),
            'target_date' => $request->get('target_date')
        ]);

        if ($request->has('attachments')) {

            $files = $request->file('attachments');

            $count = count($files);

            for ($i = 0; $i < $count; $i++) {
                $attachment = new TaskAttachment();

                $attachment->uploaded_by = $request->user()->id;
                $attachment->task_id = $task->id;

                $name = Storage::putFile("task-attachments/$task->id", $files[$i]);

                $attachment->file = $name;

                $attachment->file_name = pathinfo($files[$i]->getClientOriginalName(), PATHINFO_FILENAME);

                $attachment->file_ext = $files[$i]->getClientOriginalExtension();

                $attachment->save();
            }
        }

        return response()->json(
            $task
        );

    }

    //TODO validate that user is contactable
    public function reassign(ReassignTaskRequest $request, Task $task, $user_id)
    {
        $task->assigned_to = (int)$user_id;
        $task->save();

        return response()->json("Success");
    }

    /**
     * Mark a task as completed
     * @param CompleteTaskRequest $request
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function complete(CompleteTaskRequest $request, Task $task)
    {

        $task->completion_date = $request->get('completion_date');
        $task->completion_description = $request->get('completion_description');
        $task->status = "completed";
        $task->save();

        if ($request->has('attachments')) {

            $files = $request->file('attachments');

            $count = count($files);

            for ($i = 0; $i < $count; $i++) {
                $attachment = new TaskAttachment();

                $attachment->uploaded_by = $request->user()->id;
                $attachment->task_id = $task->id;

                $name = Storage::putFile("task-attachments/$task->id", $files[$i]);

                $attachment->file = $name;

                $attachment->file_name = pathinfo($files[$i]->getClientOriginalName(), PATHINFO_FILENAME);

                $attachment->file_ext = $files[$i]->getClientOriginalExtension();

                $attachment->type = "assignee";

                $attachment->save();
            }
        }

        return response()->json(
            $task
        );
    }

    /**
     * Approve completed task
     *
     * //TODO validate that task has been completed before approval is allowed
     * @param ApproveTaskRequest $request
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve(ApproveTaskRequest $request, Task $task)
    {

        $task->is_approved = 1;
        $task->approved_at = now();
        $task->status = "approved";
        $task->approved_by = $request->user()->id;

        $task->save();

        return response()->json($task);

    }

    /**
     * Delete task
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Task $task)
    {

        if ((int)Auth::id() !== (int)$task->assigned_by) {
            return response()->json("Not Authorized", 403);
        }

        $task->delete();
        return response()->json('Deleted');

    }

    public function types()
    {
        return response()->json(TaskType::get());
    }

}
