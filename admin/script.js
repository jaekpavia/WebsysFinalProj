function openDocumentPanel() {
  document.getElementById("add-document-panel").classList.add("show");
  document.getElementById("panel-overlay").classList.add("show");
}

function closeDocumentPanel() {
  document.getElementById("add-document-panel").classList.remove("show");
  document.getElementById("panel-overlay").classList.remove("show");
}

function openDetailsPanel(
  trackingNumber,
  title,
  description,
  sender,
  recipient,
  status,
  dateSubmitted,
  filePath,
) {
  document.getElementById("detail-tracking-number").textContent =
    trackingNumber;
  document.getElementById("detail-title").textContent = title;
  document.getElementById("detail-description").textContent =
    description || "No description provided.";
  document.getElementById("detail-sender").textContent = sender;
  document.getElementById("detail-recipient").textContent = recipient;
  document.getElementById("detail-status").textContent = status;
  document.getElementById("detail-date").textContent = dateSubmitted;

  if (filePath) {
    document.getElementById("detail-file").innerHTML =
      `<a href="../${filePath}" target="_blank">Open attached file</a>`;
  } else {
    document.getElementById("detail-file").textContent = "No file attached.";
  }

  document.getElementById("details-panel").classList.add("show");
  document.getElementById("details-overlay").classList.add("show");
}

function closeDetailsPanel() {
  document.getElementById("details-panel").classList.remove("show");
  document.getElementById("details-overlay").classList.remove("show");
}

function openEditPanel(id, title, desc, sender, recipient) {
  document.getElementById("edit-id").value = id;
  document.getElementById("edit-title").value = title;
  document.getElementById("edit-description").value = desc;
  document.getElementById("edit-sender").value = sender;
  document.getElementById("edit-recipient").value = recipient;

  document.getElementById("edit-document-panel").classList.add("show");
  document.getElementById("panel-overlay").classList.add("show");
}

function closeEditPanel() {
  document.getElementById("edit-document-panel").classList.remove("show");
  document.getElementById("panel-overlay").classList.remove("show");
}

function openDeleteModal(id, title) {
  document.getElementById("delete-id").value = id;
  document.getElementById("delete-doc-title").textContent = title;

  document.getElementById("delete-modal").classList.add("show");
  document.getElementById("delete-overlay").classList.add("show");
}

        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.remove('show');
            document.getElementById('delete-overlay').classList.remove('show');
        }

        function openHardDeleteModal(id, title) {
            document.getElementById('hard-delete-id').value = id;
            document.getElementById('hard-delete-doc-title').textContent = title;
    
            document.getElementById('hard-delete-modal').classList.add('show');
        document.getElementById('hard-delete-overlay').classList.add('show');
        }

        function closeHardDeleteModal() {
            document.getElementById('hard-delete-modal').classList.remove('show');
            document.getElementById('hard-delete-overlay').classList.remove('show');
        }
        
        /* =========================================
   DARK MODE LOGIC
   ========================================= */
// Check if the user already chose dark mode in the past
if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark-mode");
    document.getElementById("theme-icon").textContent = "☀️";
}

function toggleDarkMode() {
    const body = document.body;
    const icon = document.getElementById("theme-icon");
    
    body.classList.toggle("dark-mode");
    
    // Save their preference to the browser so it remembers!
    if (body.classList.contains("dark-mode")) {
        localStorage.setItem("theme", "dark");
        icon.textContent = "☀️";
    } else {
        localStorage.setItem("theme", "light");
        icon.textContent = "🌙";
    }
}

const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('document-file');
const fileNameDisplay = document.getElementById('file-name-display');

if (dropZone && fileInput) {
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            updateFileName(fileInput);
        }
    });
}

function updateFileName(input) {
    if (input.files && input.files.length > 0) {
        document.getElementById('file-name-display').textContent = "Selected: " + input.files[0].name;
    }
}