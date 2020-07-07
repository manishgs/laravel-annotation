<?php
namespace App\Http\Controllers;

use App\Stamp;
use DB;
use Illuminate\Http\Request;

class StampController extends Controller {
    /**
     * Display a listing of the stamps for a specific page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($pdf, $page) {
        $data = Stamp::with('creator')->where('pdf_id', $pdf)->where('page', $page)->get();
        $stamps = [];
        foreach ($data as $stamp) {
            $stamp->created_by = ['id' => $stamp->creator->id, 'name' => $stamp->creator->username];
            $stamp->type = DB::table('tbl_stamps_signatures')->select('stamp_type')->where('stamp_id', $stamp->stamp_image_id)->first();
            $stamp->created_date = $stamp->created_at->format('h:i a, M d, Y');
            unset($stamp->creator);
            unset($stamp->created_at);
            unset($stamp->updated_at);
            unset($stamp->updated_by);
            $stamps[] = $stamp;
        }
        $digita_signed = 0;
        if ($page) {
            $string = 'adbe.pkcs7.detached';
            $signed_data = DB::table('tbl_documents')->select('document_file_name')->where('document_id', $pdf)->first();
            if ($signed_data) {
                $filename = $signed_data->document_file_name;
                $fullpath = config('app.base_path') . "{$filename}";
                if ($filename && file_exists($fullpath)) {
                    $handle = fopen($fullpath, 'r');
                    while (($buffer = fgets($handle)) !== false) {
                        if (strpos($buffer, $string) !== false) {
                            $digita_signed = 1;
                            break; // Once you find the string, you should break out the loop.
                        }
                    }
                    fclose($handle);
                }
            }
        }

        return response()->json(['total' => $data->count(), 'rows' => $stamps, 'digita_signed' => $digita_signed]);
    }

    /**
     * Store a newly created stamp.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $data = $request->input();
        try {
            $stamp = [
                'created_by' => \Auth::id(),
                'pdf_id' => $data['pdf_id'],
                'page' => $data['page'],
                'position' => $data['position'],
                'stamp_image_id' => $data['stamp_image_id'],
            ];
            $stamp = Stamp::create($stamp);
            $stamp->created_by = ['id' => \Auth::user()->id, 'username' => \Auth::user()->user_full_name];
            $stamp->created_date = $stamp->created_at->format('h:i a, M d, Y');
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
    public function update($id, Request $request) {
        $data = $request->input();
        $update = Stamp::where('id', $id)->update(['position' => json_encode($data['position'])]);
        if ($update) {
            try {
                $stamp = Stamp::with('creator')->find($id);
                return response()->json(['id' => $stamp->id]);
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
    public function delete($id) {
        try {
            if (Stamp::destroy($id)) {
                return response()->json(['status' => 'OK']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }

        return response()->json(['status' => 'error', 'message' => 'Could not find the stamp.'], 400);
    }
}