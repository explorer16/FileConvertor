<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Converter</title>
    <!-- Подключаем пользовательские стили -->
    <link href="styles.css" rel="stylesheet">
    <!-- Подключаем стили Bootstrap -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Подключаем иконки Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6bbdff, #005bea);
            color: #fff;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            box-shadow: 0px 0px 20px 0px rgba(0,0,0,0.1);
            width: 100%;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <h1 class="card-header text-center">File Converter</h1>
                <div class="card-body">
                    <form id="conversionForm">
                        <div class="form-group">
                            <label for="fileInput">Select File:</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="fileInput" required>
                                <label class="custom-file-label" for="fileInput">Choose file</label>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="from">From:</label>
                                <select class="custom-select" id="from" name="from" required>
                                    <option selected disabled value="">Choose...</option>
                                    <option value="xml">XML</option>
                                    <option value="sql">SQL</option>
                                    <option value="csv">CSV</option>
                                    <!-- Добавьте другие расширения по вашему усмотрению -->
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="to">To:</label>
                                <select class="custom-select" id="to" name="to" required>
                                    <option selected disabled value="">Choose...</option>
                                    <option value="xml">XML</option>
                                    <option value="sql">SQL</option>
                                    <option value="csv">CSV</option>
                                    <!-- Добавьте другие расширения по вашему усмотрению -->
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Convert <i class="fas fa-file-export"></i></button>
                    </form>
                    <div id="conversionMessage" class="mt-3 text-center"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Скрипты Bootstrap и jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- Скрипт для иконок Font Awesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

<script>
    document.getElementById('fileInput').addEventListener('change', function(event) {
        var fileName = event.target.files[0].name;
        var label = event.target.nextElementSibling;
        label.innerHTML = fileName;
    });

    document.getElementById('conversionForm').addEventListener('submit', function(event) {
        event.preventDefault();
        convertFile();
    });

    function convertFile() {
        const fileInput = document.getElementById('fileInput');
        const fromInput = document.getElementById('from');
        const toInput = document.getElementById('to');

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}'); // Добавляем CSRF-токен
        formData.append('file', fileInput.files[0]);
        formData.append('from', fromInput.value);
        formData.append('to', toInput.value);

        fetch('/convert', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = fileInput.files[0].name.replace(/\.[^/.]+$/, `.${toInput.value}`);
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                document.getElementById('conversionMessage').innerHTML = "<i class='fas fa-check-circle'></i> Conversion successful!";
            })
            .catch(error => {
                console.error('There was a problem with the conversion:', error);
                document.getElementById('conversionMessage').innerHTML = "<i class='fas fa-times-circle'></i> Conversion failed. Please try again.";
            });
    }
</script>
</body>
</html>
