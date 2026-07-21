<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;

class MetodeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function read(){
        $metode = DB::table('metode')->orderBy('id','DESC')->get();

        return view('admin.metode.index',['metode'=>$metode]);
    }

    public function add(){
        return view('admin.metode.tambah');
    }

    public function create(Request $request){
        DB::table('metode')->insert([  
            'nama' => $request->nama]);

        return redirect('/admin/metode')->with("success","Data Berhasil Ditambah !");
    }

    public function edit($id){
        $metode = DB::table('metode')->where('id',$id)->first();
        
        return view('admin.metode.edit',['metode'=>$metode]);
    }

    public function update(Request $request, $id) {
        DB::table('metode')  
            ->where('id', $id)
            ->update([
            'nama' => $request->nama]);

        return redirect('/admin/metode')->with("success","Data Berhasil Diupdate !");
    }

    public function delete($id)
    {
        DB::table('metode')->where('id',$id)->delete();

        return redirect('/admin/metode')->with("success","Data Berhasil Dihapus !");
    }
}
