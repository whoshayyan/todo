<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Dotenv\Parser\Value;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $task = Task::where([
            'status'=>false,
            'parent_id'=>null,
        ])->orderBy('due_date')->get();

        return $task;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[

            'name' => 'required|string|max:250',

        ]);

        if($validator->fails()){
            return response()->json(['message'=>'Validation errors' , 'errors'=>$validator->messages()],422);
        }
        if($request->parent_id){
            $parent_id = $request->parent_id;
        }
        else{
            $parent_id = null;
        }

        $task = Task::create([
            'parent_id' => $parent_id,
            'name' => $request->name,
            'due_date' => Carbon::now(),
        ]);

        return $task;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $task = Task::where('id',$request->id)->first();
        return $task;
    }

    public function complete(Request $request){
        $task = Task::where('id',$request->id)->first();;


        $task->update([
            'status'=> true,
        ]);
        $task->save;
        return $task;

       /* return response()->json([
            'message'=>'Task is completed'
        ],200);*/

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $task = Task::where('id',$request->id)->first();
        $task->delete();
        return response()->json([
            'message'=>'Task is deleted'
        ],200);

    }

    public function filter(Request $request)
    {
        $tasks = Task::with('subtasks')
            ->where([
                'parent_id' => null,
            ])
            ->when($request->query('title'), function ($query, $title) {
                return $query->where('title', 'like', "%{$title}%");
            })
            ->when($request->query('due_date'), function ($query, $due) {
                if ($due === 'today') {
                    return $query->where('due_date', Carbon::today());
                } elseif ($due === 'this_week') {
                    return $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()]);
                } elseif ($due === 'next_week') {
                    return $query->whereBetween('due_date', [now()->addDays(7)->startOfWeek(), now()->addDays(7)->endOfWeek()]);
                } elseif ($due === 'overdue') {
                    return $query->where('due_date', '<', now());
                }

                return $query;
            })
            ->orderBy('due_date')
            ->paginate(15);

        return $this->setSuccessResponse($tasks->toArray());
    }
}
