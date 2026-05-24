<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login-register/login.php");
    exit();
}

if (isset($_POST['update_status'])) {
    $documentId = $_POST['document_id'];
    $newStatus = $_POST['status'];

    $updateStatus = $conn->prepare("UPDATE documents SET status = ? WHERE id = ?");
    $updateStatus->bind_param("si", $newStatus, $documentId);
    $updateStatus->execute();

    header("Location: dashboard.php");
    exit();
}

if (isset($_POST['delete_document'])) {
    $documentId = $_POST['document_id'];

    $softDelete = $conn->prepare("UPDATE documents SET is_deleted = 1 WHERE id = ?");
    $softDelete->bind_param("i", $documentId);
    $softDelete->execute();

    header("Location: dashboard.php");
    exit();
}

if (isset($_POST['add_document'])) {
    $trackingNumber = "DOC-" . date("Ymd") . "-" . rand(1000, 9999);
    $title = trim($_POST['document_title']);
    $description = trim($_POST['description']);
    $sender = trim($_POST['sender']);
    $recipient = trim($_POST['recipient']);
    $status = "Pending";

    $fileName = null;
    $filePath = null;

    if (!empty($_FILES['document_file']['name'])) {
        $uploadFolder = "../uploads/documents/";

        if (!is_dir($uploadFolder)) {
            mkdir($uploadFolder, 0777, true);
        }

        $originalFileName = basename($_FILES['document_file']['name']);
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $safeFileName = "DOC_" . time() . "_" . rand(1000, 9999) . "." . $fileExtension;
            $targetFile = $uploadFolder . $safeFileName;

            if (move_uploaded_file($_FILES['document_file']['tmp_name'], $targetFile)) {
                $fileName = $originalFileName;
                $filePath = "uploads/documents/" . $safeFileName;
            }
        }
    }

    $insertDocument = $conn->prepare("
        INSERT INTO documents 
        (tracking_number, title, description, sender, recipient, status, file_name, file_path) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $insertDocument->bind_param("ssssssss", $trackingNumber, $title, $description, $sender, $recipient, $status, $fileName, $filePath);
    $insertDocument->execute();

    header("Location: dashboard.php");
    exit();
}

if (isset($_POST['edit_document'])) {
    $documentId = $_POST['edit_document_id'];
    $title = trim($_POST['edit_document_title']);
    $description = trim($_POST['edit_description']);
    $sender = trim($_POST['edit_sender']);
    $recipient = trim($_POST['edit_recipient']);

    if (!empty($_FILES['edit_document_file']['name'])) {
        
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

        $uploadFolder = "../uploads/documents/";
        $originalFileName = basename($_FILES['edit_document_file']['name']);
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $safeFileName = "DOC_" . time() . "_" . rand(1000, 9999) . "." . $fileExtension;
            $targetFile = $uploadFolder . $safeFileName;

            if (move_uploaded_file($_FILES['edit_document_file']['tmp_name'], $targetFile)) {
                $fileName = $originalFileName;
                $filePath = "uploads/documents/" . $safeFileName;

                $updateDoc = $conn->prepare("UPDATE documents SET title = ?, description = ?, sender = ?, recipient = ?, file_name = ?, file_path = ? WHERE id = ?");
                $updateDoc->bind_param("ssssssi", $title, $description, $sender, $recipient, $fileName, $filePath, $documentId);
                $updateDoc->execute();
            }
        }
    } else {
        $updateDoc = $conn->prepare("UPDATE documents SET title = ?, description = ?, sender = ?, recipient = ? WHERE id = ?");
        $updateDoc->bind_param("ssssi", $title, $description, $sender, $recipient, $documentId);
        $updateDoc->execute();
    }

    header("Location: dashboard.php");
    exit();
}

$pageTitle = "Dashboard";
$adminName = $_SESSION['name'] ?? "Admin";

$totalDocuments = $conn->query("SELECT COUNT(*) AS total FROM documents WHERE is_deleted = 0")->fetch_assoc()['total'];
$pendingDocuments = $conn->query("SELECT COUNT(*) AS total FROM documents WHERE status = 'Pending' AND is_deleted = 0")->fetch_assoc()['total'];
$inProcessDocuments = $conn->query("SELECT COUNT(*) AS total FROM documents WHERE status = 'In Process' AND is_deleted = 0")->fetch_assoc()['total'];
$completedDocuments = $conn->query("SELECT COUNT(*) AS total FROM documents WHERE status = 'Completed' AND is_deleted = 0")->fetch_assoc()['total'];

$search = $_GET['search'] ?? '';

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $recentDocuments = $conn->query("
        SELECT * FROM documents
        WHERE title LIKE '%$search%' AND is_deleted = 0
        ORDER BY date_submitted DESC
    ");
} else {
    $recentDocuments = $conn->query("
        SELECT * FROM documents
        WHERE is_deleted = 0
        ORDER BY date_submitted DESC
        LIMIT 5
    ");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | SEC-LEO Document Tracking System</title>
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>

    <main class="main-content">

        <header class="page-header">
            <div>
                <p class="page-label">SEC-LEO Document Tracking System</p>
                <h1>Dashboard</h1>
            </div>

            <div class="header-actions">
                <a href="recycle_bin.php" class="details-btn" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">Recycle Bin</a>
                <button type="button" class="add-document-btn" onclick="openDocumentPanel()">Add Document</button>
                <a href="../logout.php" class="logout-btn">Logout</a>
            </div>

        </header>

        <section class="admin-card">
            <div>
                <h2>Welcome, <?php echo htmlspecialchars($adminName); ?></h2>
                <p>Manage and monitor document records from one dashboard.</p>
            </div>
        </section>

        <section class="overview-section">
            <div class="section-title">
                <h2>Document Overview</h2>
            </div>

            <div class="summary-cards">
                <article class="summary-card">
                    <span>Total Documents</span>
                    <h3><?php echo $totalDocuments; ?></h3>
                    <p>All recorded documents</p>
                </article>

                <article class="summary-card">
                    <span>Pending</span>
                    <h3><?php echo $pendingDocuments; ?></h3>
                    <p>Waiting for action</p>
                </article>

                <article class="summary-card">
                    <span>In Process</span>
                    <h3><?php echo $inProcessDocuments; ?></h3>
                    <p>Currently being handled</p>
                </article>

                <article class="summary-card">
                    <span>Completed</span>
                    <h3><?php echo $completedDocuments; ?></h3>
                    <p>Successfully processed</p>
                </article>
            </div>
        </section>

        <section class="documents-panel">
            <div class="panel-header">
                
                <div>
                    <h2>All Documents</h2>
                    <p>Latest document records will appear here.</p>
                </div>

                 <form method="GET" action="" class="search-bar">
                    <input type="text" class="search" name="search" placeholder="Search document..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Search</button>
                 </form>
            </div>

           

            <div class="document-list">
                <?php if ($recentDocuments->num_rows === 0) { ?>
                    <div class="empty-state">
                        <h3>No documents added yet.</h3>
                        <p>Click the Add Document button to create a document record.</p>
                    </div>
                <?php } else { ?>
                    <?php while ($document = $recentDocuments->fetch_assoc()) { ?>
                        <div class="document-row">
                            <div>
                                <h3><?php echo htmlspecialchars($document["title"]); ?></h3>
                                <p><?php echo htmlspecialchars($document["tracking_number"]); ?></p>
                            </div>

                            <form class="status-form" action="dashboard.php" method="POST">
                                <input type="hidden" name="document_id" value="<?php echo $document['id']; ?>">

                                <select name="status" onchange="this.form.submit()">
                                    <option value="Pending" <?php echo $document["status"] === "Pending" ? "selected" : ""; ?>>
                                        Pending
                                    </option>

                                    <option value="In Process" <?php echo $document["status"] === "In Process" ? "selected" : ""; ?>>
                                        In Process
                                    </option>

                                    <option value="Completed" <?php echo $document["status"] === "Completed" ? "selected" : ""; ?>>
                                        Completed
                                    </option>
                                </select>

                                <input type="hidden" name="update_status" value="1">
                            </form>

                            <span><?php echo htmlspecialchars($document["date_submitted"]); ?></span>

                            <div class="document-actions">

                                <button
                                     type="button"
                                     class="edit-btn"
                                     onclick='openEditPanel(
                                        <?php echo json_encode($document["id"]); ?>,
                                        <?php echo json_encode($document["title"]); ?>,
                                        <?php echo json_encode($document["description"]); ?>,
                                        <?php echo json_encode($document["sender"]); ?>,
                                        <?php echo json_encode($document["recipient"]); ?>
                                    )'>
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    class="details-btn"
                                    onclick='openDetailsPanel(
                                        <?php echo json_encode($document["tracking_number"]); ?>,
                                        <?php echo json_encode($document["title"]); ?>,
                                        <?php echo json_encode($document["description"]); ?>,
                                        <?php echo json_encode($document["sender"]); ?>,
                                        <?php echo json_encode($document["recipient"]); ?>,
                                        <?php echo json_encode($document["status"]); ?>,
                                        <?php echo json_encode($document["date_submitted"]); ?>,
                                        <?php echo json_encode($document["file_path"] ?? ""); ?>
                                    )'>
                                    View Details
                                </button>

                            <button 
                                type="button" 
                                class="delete-btn" 
                                onclick="openDeleteModal(<?php echo $document['id']; ?>, '<?php echo htmlspecialchars($document['title'], ENT_QUOTES, 'UTF-8'); ?>')">
                                Delete
                            </button>

                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </section>

    </main>

    <div class="panel-overlay" id="panel-overlay" onclick="closeDocumentPanel(); closeEditPanel(); closeDetailsPanel()"></div>

    <aside class="add-document-panel" id="add-document-panel">
        <div class="panel-top">
            <div>
                <h2>Add Document</h2>
                <p>Create a new document record and attach a file.</p>
            </div>

            <button type="button" class="close-panel-btn" onclick="closeDocumentPanel()">×</button>
        </div>

        <form class="document-form" action="dashboard.php" method="POST" enctype="multipart/form-data">
            <label for="document-title">Document Title</label>
            <input type="text" id="document-title" name="document_title" placeholder="Enter document title" required>

            <label for="document-description">Description</label>
            <textarea id="document-description" name="description" placeholder="Enter document description"></textarea>

            <label for="sender">Sender</label>
            <input type="text" id="sender" name="sender" placeholder="Enter sender name or office" required>

            <label for="recipient">Recipient</label>
            <input type="text" id="recipient" name="recipient" placeholder="Enter recipient name or office" required>

            <label for="document-file">Attach File</label>
            <input type="file" id="document-file" name="document_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">

            <div class="form-actions">
                <button type="button" class="cancel-btn" onclick="closeDocumentPanel()">Cancel</button>
                <button type="submit" name="add_document" class="save-btn">Save Document</button>
            </div>
        </form>
    </aside>

    <aside class="edit-document-panel" id="edit-document-panel">
    <div class="panel-top">
        <div>
            <h2>Edit Document</h2>
            <p>Update the details or replace the attached file.</p>
        </div>
        <button type="button" class="close-panel-btn" onclick="closeEditPanel()">×</button>
    </div>

    <form class="document-form" action="dashboard.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" id="edit-id" name="edit_document_id">

        <label for="edit-title">Document Title</label>
        <input type="text" id="edit-title" name="edit_document_title" required>

        <label for="edit-description">Description</label>
        <textarea id="edit-description" name="edit_description"></textarea>

        <label for="edit-sender">Sender</label>
        <input type="text" id="edit-sender" name="edit_sender" required>

        <label for="edit-recipient">Recipient</label>
        <input type="text" id="edit-recipient" name="edit_recipient" required>

        <label for="edit-file">Replace File (Leave blank to keep current file)</label>
        <input type="file" id="edit-file" name="edit_document_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">

        <div class="form-actions">
            <button type="button" class="cancel-btn" onclick="closeEditPanel()">Cancel</button>
            <button type="submit" name="edit_document" class="save-btn">Save Changes</button>
        </div>
    </form>
    </aside>

    <div class="details-overlay" id="details-overlay" onclick="closeDetailsPanel()"></div>

    <aside class="details-panel" id="details-panel">
        <div class="details-top">
            <div>
                <h2>Document Details</h2>
                <p>Full information about this document record.</p>
            </div>

            <button type="button" class="close-details-btn" onclick="closeDetailsPanel()">×</button>
        </div>

        <div class="details-card">
            <div class="detail-item">
                <span>Tracking Number</span>
                <p id="detail-tracking-number"></p>
            </div>

            <div class="detail-item">
                <span>Document Title</span>
                <p id="detail-title"></p>
            </div>

            <div class="detail-item">
                <span>Description</span>
                <p id="detail-description"></p>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <span>Sender</span>
                    <p id="detail-sender"></p>
                </div>

                <div class="detail-item">
                    <span>Recipient</span>
                    <p id="detail-recipient"></p>
                </div>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <span>Status</span>
                    <p id="detail-status"></p>
                </div>

                <div class="detail-item">
                    <span>Date Submitted</span>
                    <p id="detail-date"></p>
                </div>
            </div>

            <div class="detail-item">
            <span>Attached File</span>
            <p id="detail-file"></p>
        </div>
    </div>
    </aside> <div class="delete-overlay" id="delete-overlay" onclick="closeDeleteModal()"></div>

<div class="delete-modal" id="delete-modal">
    <div>
        <h2>Delete Document?</h2>
        <p>Are you sure you want to delete <strong id="delete-doc-title"></strong>? This will put the file in the recycle bin.</p>
    </div>

    <form action="dashboard.php" method="POST">
        <input type="hidden" id="delete-id" name="document_id">
        <div class="form-actions">
            <button type="button" class="cancel-btn" onclick="closeDeleteModal()">Cancel</button>
            <button type="submit" name="delete_document" class="delete-confirm-btn">Yes, Delete</button>
        </div>
    </form>
</div>

<script src="script.js"></script>
</body>
</html>