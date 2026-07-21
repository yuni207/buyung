<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;

class SatuanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function read(){
        $satuan = DB::table('satuan')->orderBy('id','DESC')->get();

        return view('admin.satuan.index',['satuan'=>$satuan]);
    }

    public function add(){
        return view('admin.satuan.tambah');
    }

    public function create(Request $request){
        DB::table('satuan')->insert([  
            'nama' => $request->nama]);

        return redirect('/admin/satuan')->with("success","Data Berhasil Ditambah !");
    }

    public function edit($id){
        $satuan = DB::table('satuan')->where('id',$id)->first();
        
        return view('admin.satuan.edit',['satuan'=>$satuan]);
    }

    public function update(Request $request, $id) {
        DB::table('satuan')  
            ->where('id', $id)
            ->update([
            'nama' => $request->nama]);

        return redirect('/admin/satuan')->with("success","Data Berhasil Diupdate !");
    }

    public function delete($id)
    {
        DB::table('satuan')->where('id',$id)->delete();

        return redirect('/admin/satuan')->with("success","Data Berhasil Dihapus !");
    }
}