
        function openDocumentPanel() {
            document.getElementById("add-document-panel").classList.add("show");
            document.getElementById("panel-overlay").classList.add("show");
        }

        function closeDocumentPanel() {
            document.getElementById("add-document-panel").classList.remove("show");
            document.getElementById("panel-overlay").classList.remove("show");
        }

        function openDetailsPanel(trackingNumber, title, description, sender, recipient, status, dateSubmitted, filePath) {
            document.getElementById("detail-tracking-number").textContent = trackingNumber;
            document.getElementById("detail-title").textContent = title;
            document.getElementById("detail-description").textContent = description || "No description provided.";
            document.getElementById("detail-sender").textContent = sender;
            document.getElementById("detail-recipient").textContent = recipient;
            document.getElementById("detail-status").textContent = status;
            document.getElementById("detail-date").textContent = dateSubmitted;

            if (filePath) {
                document.getElementById("detail-file").innerHTML = `<a href="../${filePath}" target="_blank">Open attached file</a>`;
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
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-title').value = title;
            document.getElementById('edit-description').value = desc;
            document.getElementById('edit-sender').value = sender;
            document.getElementById('edit-recipient').value = recipient;

            document.getElementById('edit-document-panel').classList.add('show');
            document.getElementById('panel-overlay').classList.add('show');
        }

        function closeEditPanel() {
            document.getElementById('edit-document-panel').classList.remove('show');
            document.getElementById('panel-overlay').classList.remove('show');
        }

        function openDeleteModal(id, title) {
            document.getElementById('delete-id').value = id;
            document.getElementById('delete-doc-title').textContent = title;
    
            document.getElementById('delete-modal').classList.add('show');
            document.getElementById('delete-overlay').classList.add('show');
        }

        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.remove('show');
            document.getElementById('delete-overlay').classList.remove('show');
        }