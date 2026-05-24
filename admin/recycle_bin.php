<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login-register/login.php");
    exit();
}

if (isset($_POST['restore_document'])) {
    $documentId = $_POST['document_id'];
    $restoreDoc = $conn->prepare("UPDATE documents SET is_deleted = 0 WHERE id = ?");
    $restoreDoc->bind_param("i", $documentId);
    $restoreDoc->execute();

    header("Location: recycle_bin.php");
    exit();
}

if (isset($_POST['hard_delete'])) {
    $documentId = $_POST['document_id'];

    $getFile = $conn->prepare("SELECT file_path FROM documents WHERE id = ?");
    $getFile->bind_param("i", $documentId);
    $getFile->execute();
    $fileResult = $getFile->get_result();

    if ($fileResult->num_rows > 0) {
        $fileData = $fileResult->fetch_assoc();
        if (!empty($fileData['file_path'])) {
            $realFilePath = "../" . $fileData['file_path'];
            if (file_exists($realFilePath)) {
                unlink($realFilePath);
            }
        }
    }

    $deleteDoc = $conn->prepare("DELETE FROM documents WHERE id = ?");
    $deleteDoc->bind_param("i", $documentId);
    $deleteDoc->execute();

    header("Location: recycle_bin.php");
    exit();
}

$pageTitle = "Recycle Bin";
$adminName = $_SESSION['name'] ?? "Admin";

$trashedDocuments = $conn->query("SELECT * FROM documents WHERE is_deleted = 1 ORDER BY date_submitted DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recycle Bin | SEC-LEO Document Tracking System</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <main class="main-content">
        <header class="page-header">
            <div>
                <p class="page-label">SEC-LEO Document Tracking System</p>
                <h1>Recycle Bin</h1>
            </div>
            <div class="header-actions">
                <a href="dashboard.php" class="logout-btn" style="text-decoration: none; display: inline-flex; justify-content: center;">Back to Dashboard</a>
            </div>
        </header>

        <section class="documents-panel">
            <div class="panel-header">
                <div>
                    <h2>Trashed Documents</h2>
                    <p>Documents here are hidden from the main dashboard. They can be restored or permanently deleted.</p>
                </div>
            </div>

            <div class="document-list">
                <?php if ($trashedDocuments->num_rows === 0) { ?>
                    <div class="empty-state">
                        <h3>The recycle bin is empty.</h3>
                        <p>Deleted documents will appear here.</p>
                    </div>
                <?php } else { ?>
                    <?php while ($document = $trashedDocuments->fetch_assoc()) { ?>
                        <div class="document-row">
                            <div>
                                <h3><?php echo htmlspecialchars($document["title"]); ?></h3>
                                <p><?php echo htmlspecialchars($document["tracking_number"]); ?></p>
                            </div>
                            
                            <span><?php echo htmlspecialchars($document["sender"]); ?> &rarr; <?php echo htmlspecialchars($document["recipient"]); ?></span>
                            <span><?php echo htmlspecialchars($document["date_submitted"]); ?></span>

                            <div class="document-actions">
                                <form action="recycle_bin.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="document_id" value="<?php echo $document['id']; ?>">
                                    <button type="submit" name="restore_document" class="restore-btn">Restore</button>
                                </form>

                                <form action="recycle_bin.php" method="POST" style="margin: 0;" onsubmit="return confirm('PERMANENTLY delete this document? This cannot be undone.');">
                                    <input type="hidden" name="document_id" value="<?php echo $document['id']; ?>">
                                    <button type="submit" name="hard_delete" class="delete-btn">Permanent Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </section>
    </main>
</body>
</html>