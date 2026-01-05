<?php

namespace App\Http\Controllers\Api;

use App\Enums\PermissionType;
use App\Helper\Reply;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subject\StoreRequest;
use App\Http\Requests\Subject\UpdateRequest;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->getUser();
        abort_if(!$user->hasPermission(PermissionType::SUBJECT_VIEW), 403);

        try {
            $subjects = Subject::select(['id', 'shortcode', 'name', 'created_at', 'updated_at']);
            if ($request->input('search') != null) {
                $subjects = $subjects->whereFullText(Subject::FULLTEXT, $request->input('search'));
            }
            $subjects = $subjects
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($subject) {
                    return [
                        'id' => (int) $subject->id,
                        'shortcode' => (string) $subject->shortcode,
                        'name' => (string) $subject->name,
                    ];
                })
                ->values()
                ->all();
            return Reply::successWithData($subjects, '');
        } catch (\Exception $error) {
            return $this->handleException($error);
        }
    }

    public function store(StoreRequest $request)
    {
        $user = $this->getUser();
        abort_if(!$user->hasPermission(PermissionType::SUBJECT_CREATE), 403);

        DB::beginTransaction();
        try {
            $data = $request->validated();
            Subject::create($data);
            DB::commit();
            return Reply::successWithMessage(trans('app.successes.record_save_success'));
        } catch (\Exception $error) {
            DB::rollBack();
            return $this->handleException($error);
        }
    }

    public function show(string $id)
    {
        $user = $this->getUser();
        abort_if(!$user->hasPermission(PermissionType::SUBJECT_VIEW), 403);

        try {
            $subject = Subject::with([
                'chapters' => function ($query) {
                    $query->withCount(['questions']);
                }
            ])->findOrFail($id);
            return Reply::successWithData($subject, '');
        } catch (\Exception $error) {
            return $this->handleException($error);
        }
    }

    public function update(UpdateRequest $request, string $id)
    {
        $user = $this->getUser();
        abort_if(!$user->hasPermission(PermissionType::SUBJECT_UPDATE), 403);

        DB::beginTransaction();
        try {
            $subject = Subject::findOrFail($id);
            $data = $request->validated();
            $subject->update($data);
            DB::commit();
            return Reply::successWithMessage(trans('app.successes.record_save_success'));
        } catch (\Exception $error) {
            DB::rollBack();
            return $this->handleException($error);
        }
    }

    public function destroy(string $id)
    {
        $user = $this->getUser();
        abort_if(!$user->hasPermission(PermissionType::SUBJECT_DELETE), 403);

        DB::beginTransaction();
        try {
            $subject = Subject::findOrFail($id);
            $subject->delete();
            DB::commit();
            return Reply::successWithMessage(trans('app.successes.record_delete_success'));
        } catch (\Exception $error) {
            DB::rollBack();
            return $this->handleException($error);
        }
    }

    public function autocomplete(Request $request)
    {
        $user = $this->getUser();
        abort_if(!$user->hasPermission(PermissionType::SUBJECT_VIEW), 403);

        try {
            $users = Subject::search($request->input('search'))
                ->take($this->autoCompleteResultLimit)
                ->get();
            return Reply::successWithData($users, '');
        } catch (\Exception $error) {
            return $this->handleException($error);
        }
    }

}
