<?php

namespace App\Http\Controllers;

use App\Models\AvailableModule;
use App\Models\Package;
use App\Models\PackageModule;
use App\Models\School;
use Illuminate\Http\Request;

class PackagesController extends Controller
{
    //
    public function index()
    {
        $packages = Package::with(['packageModules.module', 'schools' => function ($q) {
            $q->where('is_active', '1');
        }])->orderBy('name')->get();
        return response()->json(compact('packages'), 200);
    }
    public function store(Request $request)
    {
        $name = $request->name;
        $package = Package::where('name', $name)->first();
        if (!$package) {
            $package = new Package();
            $package->name = $name;
            $package->description = $request->description;
            $package->save();
        }
        return $this->index();
    }
    public function update(Request $request, Package $package)
    {
        $package->name = $request->name;
        $package->description = $request->description;
        $package->save();
        return $this->index();
    }
    public function fetchModules()
    {
        $modules = AvailableModule::where('status', 'Ready')->get();
        return response()->json(compact('modules'), 200);
    }
    public function addModule(Request $request)
    {
        $package_id = $request->package_id;
        $module_ids = json_decode(json_encode($request->module_ids));
        foreach ($module_ids as $module_id) {

            $package_module = PackageModule::where(['package_id' => $package_id, 'module_id' => $module_id])->first();
            if (!$package_module) {
                $package_module = new PackageModule();
                $package_module->package_id = $package_id;
                $package_module->module_id = $module_id;
                $package_module->save();
            }
        }
        return $this->index();
    }
    public function removeModule(Request $request, PackageModule $package_module)
    {
        $package_module->delete();
        return $this->index();
    }

    public function assignSchoolPackage(Request $request)
    {
        $package_id = $request->package_id;
        $school_ids = json_decode(json_encode($request->school_ids));
        foreach ($school_ids as $school_id) {

            $school = School::find($school_id);
            $school->package_id = $package_id;
            $school->save();
        }
        return $this->index();
    }
}
