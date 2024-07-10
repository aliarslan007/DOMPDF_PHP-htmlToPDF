<!DOCTYPE html>
<html>
<head>
    <title>Upload Form</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background-color: #f8f9fa; }
        .container { max-width: 600px; border: 1px solid #dddddd; padding: 20px; background-color: #ffffff; }
        .file-form {
            margin-bottom: 20px;
            border: 2px dashed #ddd;
            padding: 20px;
            cursor: pointer;
        }
        .file-form p {
            margin-bottom: 5px;
        }
        .file-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .file-preview img {
            width: 150px;
            height: auto;
            border: 1px solid #ddd;
            padding: 5px;
        }
        input[type="file"] { display: none; }
        .btn-primary { background-color: #007bff; border-color: #007bff; color: #fff; transition: background-color .15s; }
        .btn-primary:hover { background-color: #0069d9; border-color: #0062cc; }
        .highlight { border-color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Upload Form</h2>
        <form method="POST" enctype="multipart/form-data" action="upload.php">
            <!-- Site Name -->
            <div class="form-group row">
                <label for="site_name" class="col-sm-4 col-form-label">Site Name:</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="site_name" name="site_name" required>
                </div>
            </div>
            <!-- Tech Name -->
            <div class="form-group row">
                <label for="solucomp_tech" class="col-sm-4 col-form-label">Tech Name:</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="solucomp_tech" name="solucomp_tech" required>
                </div>
            </div>
            <!-- Engineer Contact -->
            <div class="form-group row">
                <label for="engineer_contact" class="col-sm-4 col-form-label">Engineer Contact:</label>
                <div class="col-sm-8">
                    <input type="email" class="form-control" id="engineer_contact" name="engineer_contact" required>
                </div>
            </div>
            <!-- Customer Engineer Name -->
            <div class="form-group row">
                <label for="customer_engineer_name" class="col-sm-4 col-form-label">Customer Engineer Name:</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="customer_engineer_name" name="customer_engineer_name" required>
                </div>
            </div>
            <!-- Post Install Pics -->
            <div class="file-form" id="postInstallDropArea" onclick="document.getElementById('POST_INSTALLATION_PICTURES').click();">
                <p>Drag and drop Post Installation Images here or click to select</p>
                <input type="file" id="POST_INSTALLATION_PICTURES" name="POST_INSTALLATION_PICTURES[]" multiple onchange="displayFileNames('POST_INSTALLATION_PICTURES', 'postInstallDropAreaPreview', 'postInstallDropArea')">
                <div id="postInstallDropAreaPreview" class="file-preview"></div>
            </div>
            <!-- Material Pics -->
            <div class="file-form" id="materialDropArea" onclick="document.getElementById('MATERIAL_PICTURES').click();">
                <p>Drag and drop Material Images here or click to select</p>
                <input type="file" id="MATERIAL_PICTURES" name="MATERIAL_PICTURES[]" multiple onchange="displayFileNames('MATERIAL_PICTURES', 'materialDropAreaPreview', 'materialDropArea')">
                <div id="materialDropAreaPreview" class="file-preview"></div>
            </div>
            <!-- Pre Install Pics -->
            <div class="file-form" id="preInstallDropArea" onclick="document.getElementById('PRE_INSTALLATION_PICTURES').click();">
                <p>Drag and drop Pre Install Images here or click to select</p>
                <input type="file" id="PRE_INSTALLATION_PICTURES" name="PRE_INSTALLATION_PICTURES[]" multiple onchange="displayFileNames('PRE_INSTALLATION_PICTURES', 'preInstallDropAreaPreview', 'preInstallDropArea')">
                <div id="preInstallDropAreaPreview" class="file-preview"></div>
            </div>
            <!-- Submit Button -->
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary" name="submit">Submit</button>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            ['postInstallDropArea', 'materialDropArea', 'preInstallDropArea'].forEach(areaId => {
                const area = document.getElementById(areaId);

                // Prevent default drag behaviors
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    area.addEventListener(eventName, preventDefaults, false);
                });

                // Highlight drop area when dragging over it
                ['dragenter', 'dragover'].forEach(eventName => {
                    area.addEventListener(eventName, highlight, false);
                });

                // Remove highlight when not dragging over drop area
                ['dragleave'].forEach(eventName => {
                    area.addEventListener(eventName, unhighlight, false);
                });

                // Handle dropped files
                area.addEventListener('drop', handleDrop, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            function highlight(e) {
                e.currentTarget.classList.add('highlight');
            }

            function unhighlight(e) {
                e.currentTarget.classList.remove('highlight');
            }

            function handleDrop(e) {
                e.currentTarget.classList.remove('highlight');
                preventDefaults(e);

                let area = e.currentTarget;
                let dt = e.dataTransfer;
                let files = dt.files;
                let input = area.querySelector('input[type="file"]');
                input.files = files;

                const previewContainer = document.getElementById(`${area.id}Preview`);
                previewContainer.innerHTML = '';

                // Hide the placeholder <p> tag
                area.querySelector('p').style.display = 'none';

                if (files.length) {
                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        const fileName = document.createElement('p');
                        fileName.textContent = file.name;
                        previewContainer.appendChild(fileName);
                    }
                }
            }
        });
        function displayFileNames(inputId, previewId, dropAreaId) {
            const previewContainer = document.getElementById(previewId);
            previewContainer.innerHTML = '';
            const dropArea = document.getElementById(dropAreaId);

            // Hide the placeholder <p> tag
            dropArea.querySelector('p').style.display = 'none';

            const files = document.getElementById(inputId).files;
            if (files) {
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const fileName = document.createElement('p');
                    fileName.textContent = file.name;
                    previewContainer.appendChild(fileName);
                }
            }
        }
    </script>
</body>
</html>
