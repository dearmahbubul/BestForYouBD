<?php

namespace App\Http\Controllers\API;
use App\Hospital;
use App\HospitalDepartment;
use DB;
use App\HospitalDepartmentRelation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HospitalController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (\Gate::allows('isAdmin') || \Gate::allows('isAuthor')) {
            return Hospital::with('departments')->latest()->paginate(5);
        }
    }
    public function getDept()
    {
        return HospitalDepartment::where('isActive','=','Active')->orderby('name','asc')->get();
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
           'name'=>'required|string|max:191',
        ]);
        if(!empty($request->main_img)){
            $name = time().rand(50,5000).'.' . explode('/', explode(':', substr($request->main_img, 0, strpos($request->main_img, ';')))[1])[1];
            \Image::make($request->main_img)->save(public_path('img/hospitals/').$name);
            $request->merge(['main_img' => $name]);
        }else{
            $request->merge(['main_img' => 'default.jpg']);
        }
        if(!empty($request->gallery_img_1)){
            $name = time().rand(50,5000).'.' . explode('/', explode(':', substr($request->gallery_img_1, 0, strpos($request->gallery_img_1, ';')))[1])[1];
            \Image::make($request->gallery_img_1)->save(public_path('img/hospitals/').$name);
            $request->merge(['gallery_img_1' => $name]);
        }else{
            $request->merge(['gallery_img_1' => 'default.jpg']);
        }
        if(!empty($request->gallery_img_2)){
            $name = time().rand(50,5000).'.' . explode('/', explode(':', substr($request->gallery_img_2, 0, strpos($request->gallery_img_2, ';')))[1])[1];
            \Image::make($request->gallery_img_2)->save(public_path('img/hospitals/').$name);
            $request->merge(['gallery_img_2' => $name]);
        }else{
            $request->merge(['gallery_img_2' => 'default.jpg']);
        }
        $hospital = Hospital::create([
            'name' => $request['name'],
            'estDate' => $request['estDate'],
            'address' => $request['address'],
            'city' => $request['city'],
            'main_img' => $request['main_img'],
            'gallery_img_1' => $request['gallery_img_1'],
            'gallery_img_2' => $request['gallery_img_2'],
            'type' => $request['type'],
            'isActive' => $request['isActive'],
        ]);
        $hospital->departments()->sync($request->department);
        return ['update' => "Hospital information stored"];
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getHosDept($id)
    {
        $hospital = HospitalDepartmentRelation::where('institute_id',$id)
        ->leftJoin('departments','hospital_department_relations.department_id','=','departments.id')
        ->select('hospital_department_relations.*',
                'departments.name')
        ->orderBy('departments.name', 'ASC')
        ->get();
        return $hospital;
    }
    public function updateEducationDepartment(Request $request)
    {
        $eduDept = InstituteDepartments::where('institute_id', $request->institute_id)
           ->where('department_id', $request->department_id)
           ->update($request->all());
           return ['update' => "Hospital Department information stored"];
    }
    public function addInstituteDepartment(Request $request,$institude_id)
    {
        $request->merge(['institute_id' => $institude_id]);
        $insDept = InstituteDepartments::where('institute_id', $request->institute_id)
        ->where('department_id', $request->department_id)
        ->first();
        if (!$insDept) {
            InstituteDepartments::create($request->all());
            return ['success' => "Hospital Department information updated"];
        }else{
            return redirect()->back()->withErrors("Hospital Department Already Stored");
        }
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
        $hospital = Hospital::findOrFail($id);
        $this->validate($request,[
            'name'=>'required|string|max:191'
        ]);
        $currentPhoto = $hospital->main_img;
        
        if($request->main_img != $currentPhoto){
            $name = time().rand(50,5000).'.' . explode('/', explode(':', substr($request->main_img, 0, strpos($request->main_img, ';')))[1])[1];
            \Image::make($request->main_img)->save(public_path('img/institutes/').$name);
            $request->merge(['main_img' => $name]);
            $institudePhoto = public_path('img/institutes/').$currentPhoto;
            if(file_exists($institudePhoto)){
                @unlink($institudePhoto);
            }
        }
        $currentPhoto = $hospital->gallery_img_1;
        if($request->gallery_img_1 != $currentPhoto){
            $name = time().rand(50,5000).'.' . explode('/', explode(':', substr($request->gallery_img_1, 0, strpos($request->gallery_img_1, ';')))[1])[1];

            \Image::make($request->gallery_img_1)->save(public_path('img/institutes/').$name);
            $request->merge(['gallery_img_1' => $name]);
            $institudePhoto = public_path('img/institutes/').$currentPhoto;
            if(file_exists($institudePhoto)){
                @unlink($institudePhoto);
            }
        }
        $currentPhoto = $hospital->gallery_img_2;
        if($request->gallery_img_2 != $currentPhoto){
            $name = time().rand(50,5000).'.' . explode('/', explode(':', substr($request->gallery_img_2, 0, strpos($request->gallery_img_2, ';')))[1])[1];

            \Image::make($request->gallery_img_2)->save(public_path('img/institutes/').$name);
            $request->merge(['gallery_img_2' => $name]);

            $institudePhoto = public_path('img/institutes/').$currentPhoto;
            if(file_exists($institudePhoto)){
                @unlink($institudePhoto);
            }
        }
        $hospital->update($request->all());
        if(!empty($request->department)){
            $hospital->departments()->sync($request->department);
        }
        return ['update' => "Institude information updated"];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyEducationDepartment(Request $request)
    {
        DB::table('institute_departments')->where('institute_id', $request->institute_id)->where('department_id',$request->department_id)->delete();
        return ['Message' => "Institude department information updated"];
    }
    public function destroy($id)
    {
        $user = Hospital::findOrFail($id);
        if($user->main_img != 'default.jpg'){
            $institudePhoto = public_path('img/institutes/').$user->main_img;
            if(file_exists($institudePhoto)){
                @unlink($institudePhoto);
            }
        }
        if($user->gallery_img_1 != 'default.jpg'){
            $institudePhoto = public_path('img/institutes/').$user->gallery_img_1;
            if(file_exists($institudePhoto)){
                @unlink($institudePhoto);
            }
        }
        if($user->gallery_img_2 != 'default.jpg'){
            $institudePhoto = public_path('img/institutes/').$user->gallery_img_2;
            if(file_exists($institudePhoto)){
                @unlink($institudePhoto);
            }
        }
        $user->delete();
        return ['delete'=>"Deleted successfully"];
    }
    public function search(){

        if ($search = \Request::get('q')) {
            $users = Hospital::where(function($query) use ($search){
                $query->where('name','LIKE',"%$search%")
                    ->orWhere('email','LIKE',"%$search%");
            })->paginate(20);
        }else{
            $users = User::latest()->paginate(5);
        }

        return $users;
    }
}