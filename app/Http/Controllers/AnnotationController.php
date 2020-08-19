<?php

namespace App\Http\Controllers;

use App\Annotation;
use App\Comment;
use Auth;
use DB;
use Illuminate\Http\Request;
use Input;
use View;

class AnnotationController extends Controller {
    public function index($id) {
        // Todo- get pdf detail by user id
        $data['docid'] = Input::get('dcno');
        $file = Input::get('file');
        $pdf = ['id' => $id, 'url' => $file];
        $data['pdf'] = ['id' => $id, 'url' => $file];
        if (Input::get('dcno')) {
            $data['bookmarklist'] = DB::table('tbl_document_bookmarks')->select('document_id', 'document_bookmark_id', 'document_bookmark')->where('document_id', Input::get('dcno'))->get();
        }

        /* Todo - get stamps by user id*/
        /*$data['stamps'] = [
        [
        'id' =>1,
        'value'=>'https://img.pngio.com/approved-png-png-image-with-transparent-background-completed-approved-stamp-png-840_485.png',
        'type' => 'image'
        ],
        [
        'id' =>2,
        'value'=>'Approved',
        'type' => 'text'
        ]
        ];*/

        $data['stamps'] = DB::table('tbl_stamps_signatures')->select('stamp_id as id', 'stamp_value as value', 'stamp_type as type')->whereIn('stamp_type', ['image', 'draw'])->get();
        foreach ($data['stamps'] as $key => $value) {
            if ($value->type == 'image') {
                $value->value = config('app.stamp_url') . $value->value;
            } elseif ($value->type == 'draw') {
                $value->value = config('app.sign_url') . $value->value;
            } else {
                $value->value = $value->value;
            }
        }
        /*object to multiple array*/
        $data['stamps'] = json_decode(json_encode($data['stamps']), true);

        $data['stamps_text'] = DB::table('tbl_stamps_signatures')->select('stamp_id as id', 'stamp_value as value', 'stamp_type as type')->whereIn('stamp_type', ['text'])->get();

        $data['stamps_text'] = json_decode(json_encode($data['stamps_text']), true);
        // echo '<pre>';
        // print_r($data1);
        // exit();
        //return view('annotation.index', compact('pdf', 'stamps'));
        return View::make('annotation.index')->with($data);
    }

    public function search($pdf_id, Request $request) {
        $q = $request->get('q');

        $query = Annotation::select('annotations.id', 'annotations.page', 'comments.text')->where('pdf_id', $pdf_id)
            ->leftJoin('comments', 'comments.annotation_id', '=', 'annotations.id');

        if ($q) {
            $query->whereRaw('comments.text LIKE "%' . trim($q) . '%"');
        }

        $result = $query->get();

        $ids = [];
        $data = [];
        foreach ($result as $row) {
            if (!in_array($row->id, $ids)) {
                $ids[] = $row->id;
                $data[] = $row;
            }
        }

        return response()->json(['total' => count($data), 'rows' => $data]);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function listall(Request $request) {
        $page = $request->get('page');
        $pdf_id = $request->get('pdf_id');

        $data = Annotation::select('id', 'page', 'pdf_id', 'properties', 'ranges', 'shapes', 'quote')->with('comments')->where('page', $page)->where('pdf_id', $pdf_id)->get();

        $annotations = [];

        foreach ($data as $annotation) {

            foreach ($annotation->comments as &$comment) {

                @$comment->created_date = dtFormat($comment->created_at);
                @$comment->created_by = ['id' => @$comment->user->id, 'name' => @$comment->user->user_full_name];
                //add the "name" property to the user object
                @$comment->user->name = @$comment->user->user_full_name;
                unset($comment->created_at);
                unset($comment->updated_at);
                unset($comment->annotation_id);
                unset($comment->user);
                unset($comment->user_id);

            }

            if (empty($annotation->ranges)) {
                unset($annotation->quote);
                unset($annotation->ranges);
            }

            $annotations[] = $annotation;
        }
        return response()->json(['total' => count($annotations), 'rows' => $annotations]);
    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request) {

        $data = json_decode($request->getContent(), true);
        $input = [
            'created_by' => \Auth::id(),
            'ranges' => isset($data['ranges']) ? $data['ranges'] : null,
            'shapes' => isset($data['shapes']) ? $data['shapes'] : null,
            'quote' => isset($data['quote']) ? $data['quote'] : '',
            'pdf_id' => $data['pdf_id'],
            'page' => $data['page'],
            'properties' => $data['properties'],
        ];
        try {
            $annotation = Annotation::create($input);

            $comments = [];
            if (isset($data['comments'])) {
                foreach ($data['comments'] as $d) {
                    $comment = Comment::create([
                        'annotation_id' => $annotation->id,
                        'user_id' => \Auth::id(),
                        'text' => $d['text'],
                    ]);

                    $comment->created_date = dtFormat($comment->created_at);
                    $comment->created_by = ['id' => $comment->user->id, 'name' => $comment->user->user_full_name];
                    unset($comment->created_at);
                    unset($comment->updated_at);
                    unset($comment->annotation_id);
                    unset($comment->user);
                    unset($comment->user_id);
                    $comments[] = $comment;
                }
            }
            $data_id = isset($data['id']) ? $data['id'] : time();
            return response()->json(['annotationId' => $data_id, 'id' => $annotation->id, 'comments' => $comments]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update($id, Request $request) {
        $annotation = Annotation::find($id);
        if ($annotation) {
            $data = json_decode($request->getContent(), true);
            if (isset($data['ranges'])) {
                $annotation->ranges = $data['ranges'];
                $annotation->quote = $data['quote'];
            }

            if (isset($data['shapes'])) {
                $annotation->shapes = $data['shapes'];
            }

            $annotation->page = $data['page'];
            $annotation->pdf_id = $data['pdf_id'];
            $annotation->properties = $data['properties'];

            $annotation->save();
            $comments = [];

            foreach ($annotation->comments as $comment) {
                $found = false;
                foreach ($data['comments'] as $d) {
                    if (isset($d['id']) && $d['id'] == $comment->id) {
                        $found = true;
                    }
                }

                if (!$found) {
                    Comment::destroy($comment->id);
                }
            }

            foreach ($data['comments'] as $d) {
                if (isset($d['id'])) {
                    $comment = Comment::where('id', $d['id'])->update(['text' => $d['text']]);
                    $comment = Comment::find($d['id']);
                } else {
                    $comment = Comment::create([
                        'annotation_id' => $annotation->id,
                        'user_id' => \Auth::id(),
                        'text' => $d['text'],
                    ]);
                }
                $comment->created_date = dtFormat($comment->created_at);
                $comment->created_by = ['id' => $comment->user->id, 'name' => $comment->user->user_full_name];
                unset($comment->created_at);
                unset($comment->updated_at);
                unset($comment->annotation_id);
                unset($comment->user);
                unset($comment->user_id);
                $comments[] = $comment;
            }
            try {
                return response()->json(['comments' => $comments]);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
            }
        }
        return response()->json(['status' => 'error', 'message' => 'Could not find the annotation.'], 400);
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete($id) {
        try {
            if (Annotation::destroy($id)) {
                Comment::where('annotation_id', $id)->delete();
                return response()->json(['status' => 'OK']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
        return response()->json(['status' => 'error', 'message' => 'Could not find the annotation.'], 400);
    }

    public function deleteAll($id, Request $request) {
        $annotations = $request->get('id');

        if ($annotations) {
            Annotation::where('pdf_id', $id)->whereIn('id', $annotations)->delete();
            Comment::whereIn('annotation_id', $annotations)->delete();
        } else {
            Comment::whereIn('annotation_id', Annotation::where('pdf_id', $id)->lists('id')->toArray())->delete();
            Annotation::where('pdf_id', $id)->delete();
        }
        return response()->json(['status' => 'OK']);
    }
}