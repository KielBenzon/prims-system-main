<?php

namespace App\Http\Controllers;

use App\Constant\MyConstant;
use App\Models\Mail;
use App\Services\MailService;
use App\Services\useValidator;
use Illuminate\Http\Request;
use App\Models\User;

class MailController extends Controller
{
    public function index()
    {
        $search = request('search');
        $role = request('role');
        
        $mails = $this->getPaginatedWithFallback(function () use ($search, $role) {
            return Mail::query()
                ->when($search, function ($query, $search) {
                    return $query->where('sender', 'like', '%' . $search . '%')
                        ->orWhere('recipient', 'like', '%' . $search . '%')
                        ->orWhere('priority', 'like', '%' . $search . '%')
                        ->orWhere('status', 'like', '%' . $search . '%')
                        ->orWhere('date', 'like', '%' . $search . '%');
                })
                ->when($role, function ($query, $role) {
                    return $query->whereHas('user', function ($q) use ($role) {
                        $q->where('role', $role);
                    });
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        });

        $roles = $this->executeWithFallback(function () {
            return User::where('role', 'Parishioner')->distinct()->pluck('role');
        }, collect([]));
        
        $roleEmail = $this->executeWithFallback(function () {
            return User::where('role', 'Parishioner')->pluck('email', 'role')->toArray();
        }, []);

        return view('admin.mail', compact('mails', 'roles', 'roleEmail'));
    }

    public function store(Request $request)
    {
        $result = (new MailService(new useValidator))
            ->store($request);

        if ($result['error_code'] !== MyConstant::SUCCESS_CODE) {
            return response()->json([
                'error_code' => $result['error_code'],
                'message' => $result['message'],
            ], $result['status_code']);
        }

        return redirect()->back()->with([
            'error_code' => $result['error_code'],
            'message' => $result['message'],
        ]);
    }

    public function update(Request $request, $id)
    {
        $result = (new MailService(new useValidator))
            ->update($request, $id);

        if ($result['error_code'] !== MyConstant::SUCCESS_CODE) {
            return response()->json([
                'error_code' => $result['error_code'],
                'message' => $result['message'],
            ], $result['status_code']);
        }

        return redirect()->back()->with([
            'error_code' => $result['error_code'],
            'message' => $result['message'],
        ]);
    }

    public function destroy($id)
    {
        $result = (new MailService(new useValidator))
            ->destroy($id);

        if ($result['error_code'] !== MyConstant::SUCCESS_CODE) {
            return response()->json([
                'error_code' => $result['error_code'],
                'message' => $result['message'],
            ], $result['status_code']);
        }

        return redirect()->back()->with([
            'error_code' => $result['error_code'],
            'message' => $result['message'],
        ]);
    }
}
