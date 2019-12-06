<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Stamp;
use App\Http\Requests;

class StampController extends Controller
{
    /**
     * Display a listing of the stamps for a specific page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($pdf, $page)
    {
        $data = Stamp::with('creator')->where('pdf_id', $pdf)->where('page', $page)->get();
        $stamps =[];
        foreach ($data as $stamp) {
            $stamp->created_by = ['id'=>$stamp->creator->id, 'name'=>$stamp->creator->name];
            $stamp->created_date = $stamp->created_at->timestamp . '000';
            unset($stamp->creator);
            unset($stamp->created_at);
            unset($stamp->updated_at);
            unset($stamp->updated_by);
            $stamps[] = $stamp;
        }

        return response()->json(['total' =>$data->count(), 'rows' => $stamps ]);
    }

    /**
     * Store a newly created stamp.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->input();
        try {
            $stamp = [
                'created_by'   => \Auth::id(),
                'pdf_id'    => $data['pdf_id'],
                'page' => $data['page'],
                'position' => $data['position'],
                'stamp_image_id' => $data['stamp_image_id']
            ];
            $stamp = Stamp::create($stamp);
            $stamp->created_by = ['id'=>\Auth::user()->id, 'name'=>\Auth::user()->name];
            $stamp->created_date = $stamp->created_at->timestamp . '000';
            unset($stamp->created_at);
            unset($stamp->updated_at);
            unset($stamp->updated_by);
            return response()->json($stamp);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Update stamp position
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update($id, Request $request)
    {
        $data = $request->input();
        $update = Stamp::where('id', $id)->update(['position'=>json_encode($data['position'])]);
        if ($update) {
            try {
                $stamp = Stamp::with('creator')->find($id);
                return response()->json(['id'=>$stamp->id]);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
            }
        }
        return response()->json(['status' => 'error', 'message' => 'Could not find the stamp.'], 400);
    }

    /**
     * Delete stamp
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            if (Stamp::destroy($id)) {
                return response()->json(['status'=>'OK']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }

        return response()->json(['status' => 'error', 'message' => 'Could not find the stamp.'], 400);
    }
}