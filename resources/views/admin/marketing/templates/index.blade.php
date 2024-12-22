<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Templates</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        /* General Styling */
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #1f1f33, #282846);
            color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 20px;
            background: rgba(42, 42, 60, 0.9);
            border-radius: 15px;
        }
        form label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #ddd;
        }
        form input, form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: rgba(42, 42, 60, 0.9);
            color: #fff;
        }
        .preview-container {
            margin-top: 20px;
            background: rgba(58, 58, 79, 0.9);
            padding: 15px;
            border-radius: 12px;
        }
        .preview-content {
            background: rgba(42, 42, 60, 0.8);
            padding: 10px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Email Template</h1>
        <form action="{{ route('admin.marketing.templates.store') }}" method="POST">
            @csrf
            <label for="name">Template Name</label>
            <input type="text" id="name" name="name" required>
            <label for="content">Template Content (Markdown)</label>
            <textarea id="content" name="content" required></textarea>
            <button type="submit">Save Template</button>
        </form>

        <div class="preview-container">
            <h2>Preview</h2>
            <div id="preview" class="preview-content">
                Start typing your content to see the preview...
            </div>
        </div>
    </div>

    <!-- Marked.js -->
    <script src="https://cdn.jsdelivr.net/npm/marked@4.3.0/marked.min.js"></script>

    <!-- Custom Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const contentInput = document.getElementById('content');
            const preview = document.getElementById('preview');

            if (typeof marked !== 'undefined' && typeof marked.parse === 'function') {
                contentInput.addEventListener('input', () => {
                    const markdown = contentInput.value;
                    preview.innerHTML = marked.parse(markdown);
                });
            } else {
                console.error('Marked.js is not loaded correctly or the version is incompatible.');
            }
        });
    </script>
</body>
</html>
