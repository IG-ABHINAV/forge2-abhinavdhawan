<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
class OrganizationController extends Controller {
    public function show(Request $request) {
        return response()->json($request->user()->organization);
    }
    public function update(Request $request) {
        $org = $request->user()->organization;
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'domain' => 'sometimes|string|nullable',
        ]);
        $org->update($data);
        return response()->json($org);
    }
}
