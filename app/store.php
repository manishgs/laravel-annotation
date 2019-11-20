<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
$annotations = file_get_contents('./annotation.json', true);
$annotations = json_decode($annotations);
if (!$annotations) {
    $annotations = [];
}

$stamps = file_get_contents('./stamp.json', true);
$stamps = json_decode($stamps);
if (!$stamps) {
    $stamps = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_GET['stamp'] == '1') {
        $stamp = $_POST;
        if (!$stamp['id']) {
            $stamp['id'] = rand();
            $stamps[] = $stamp;
        } else {
            foreach ($stamps as &$s) {
                if ($s->id == $stamp['id']) {
                    $s = $stamp;
                }
            }
        }
        file_put_contents('./stamp.json', json_encode($stamps));
        echo json_encode($stamp);
    } else {
        try {
            $annotation = json_decode(file_get_contents("php://input"), true);
            if (!$annotation['id']) {
                $annotation['id'] = rand();
            }

            $comments = [];
            foreach ($annotation['comments'] as $comment) {
                if ($comment && isset($comment['text'])) {
                    $comment->added_by = $comment->added_by->aid;
                    $comments[] = $comment;
                }
            }
            $annotation['comments'] = $comments;
            $annotations[] = $annotation;
            file_put_contents('./annotation.json', json_encode($annotations));
        } catch (Exception $e) {
            var_dump($e);
        }

        echo json_encode(['status' => 'success', 'id' => $annotation['id']]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if ($_GET['stamp'] == 1) {
        parse_str(file_get_contents("php://input"), $stamp);
        $stampNew = [];

        foreach ($stamps as $key => $val) {
            if ($stamp['id'] != $val->id) {
                $stampNew[] = $val;
            }
        }
        file_put_contents('./stamp.json', json_encode($stampNew));
        echo json_encode(['status' => $stampNew]);
    } elseif ($_GET['annotations'] === 'all') {
        file_put_contents('./annotation.json', json_encode([]));
        echo json_encode(['status' => 'success']);
    } else {
        $annotation = json_decode(file_get_contents("php://input"), true);
        $annotationNew = [];

        foreach ($annotations as $key => $val) {
            if ($annotation['id'] != $val->id) {
                $annotationNew[] = $val;
            }
        }
        file_put_contents('./annotation.json', json_encode($annotationNew));
        echo json_encode(['status' => 'success']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $annotation = json_decode(file_get_contents("php://input"), true);
    $annotationNew = [];

    foreach ($annotations as $key => $val) {
        if ($annotation['id'] === $val->id) {
            $comments = [];
            foreach ($annotation['comments'] as $comment) {
                if ($comment && isset($comment['text'])) {
                    $comment->added_by = $comment->added_by->aid;
                    $comments[] = $comment;
                }
            }
            $annotation['comments'] = $comments;

            $annotationNew[] = $annotation;
        } else {
            $annotationNew[] = $val;
        }
    }
    file_put_contents('./annotation.json', json_encode($annotationNew));
    echo json_encode(['status' => 'success']);
} else {
    if ($_GET['stamp'] == '1') {
        $page = $_GET['page'];
        foreach ($stamps as $a) {
            if ($a->page == $page) {
                $a->added_by = ['id' => $a->added_by, 'name' => 'Admin'];
                $new[] = $a;
            }
        }

        echo json_encode(['total' => count($new), 'rows' => $new]);
    } else {
        $page = $_GET['page'];
        $q = $_GET['search'];
        $new = [];
        foreach ($annotations as $a) {
            if ($a->page == $page) {
                $new[] = $a;
            }
            $found = false;
            foreach ($a->comments as &$comment) {
                $comment->added_by = ['id' => $comment->added_by, 'name' => 'Admin'];
                if (strstr(strtolower($comment->text), strtolower($q))) {
                    $found = true;
                }
            }
            if ($found) {
                $new[] = $a;
            }
        }
        echo json_encode(['total' => count($new), 'rows' => $new]);
    }
}