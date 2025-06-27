<?php
function getDocumentCount($conn, $type, $userId)
{
    $type = strtolower($type);
    $sql = "";

    switch ($type) {
        case 'received':
            $sql = "SELECT COUNT(*) as total FROM documents WHERE receiver_id = '$userId'";
            break;
        case 'forwarded':
            $sql = "SELECT COUNT(*) as total FROM documents WHERE sender_id = '$userId' AND status = 'forwarded'";
            break;
        case 'deferred':
            $sql = "SELECT COUNT(*) as total FROM documents WHERE status = 'deferred' AND receiver_id = '$userId'";
            break;
        case 'history':
            $sql = "SELECT COUNT(*) as total FROM documents WHERE sender_id = '$userId' OR receiver_id = '$userId'";
            break;
        default:
            return 0;
    }

    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['total'];
    }

    return 0;
}
?>