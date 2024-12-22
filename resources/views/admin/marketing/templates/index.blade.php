<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Templates</title>
    <!-- Font and CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        /* General Styles */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', sans-serif;
            color: #f0f0f0;
            background: linear-gradient(135deg, #1f1f33, #282846);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 20px;
            background: rgba(42, 42, 60, 0.9);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        h1 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #fff;
        }

        h1::after {
            content: '';
            display: block;
            width: 120px;
            height: 3px;
            margin: 10px auto 0;
            background: linear-gradient(90deg, #ff512f, #dd2476);
            border-radius: 2px;
        }

        form label {
            font-weight: 600;
            margin-bottom: 10px;
            display: block;
            color: #ddd;
        }

        form input, form textarea {
            width: 100%;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: rgba(42, 42, 60, 0.9);
            color: #fff;
            border: none;
            outline: none;
            font-size: 1rem;
        }

        form textarea {
            resize: vertical;
            height: 150px;
        }

        form input:focus, form textarea:focus {
            box-shadow: 0 0 10px rgba(255, 81, 47, 0.5);
        }

        .save-btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(90deg, #ff512f, #dd2476);
            color: #fff;
            text-transform: uppercase;
            font-weight: 600;
            border-radius: 30px;
            text-decoration: none;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 5px 15px rgba(255, 81, 47, 0.5);
        }

        .save-btn:hover {
            transform: translateY(-3px);
            background: linear-gradient(90deg, #dd2476, #ff512f);
            box-shadow: 0 8px 20px rgba(255, 81, 47, 0.7);
        }

        /* Preview Pane */
        .preview-container {
            margin-top: 20px;
            background: rgba(58, 58, 79, 0.9);
            padding: 15px;
            border-radius: 12px;
            font-size: 1rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .preview-content {
            padding: 10px;
            background: rgba(42, 42, 60, 0.8);
            border-radius: 8px;
            overflow: auto;
            max-height: 300px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Email Template</h1>

        <form id="template-form" action="{{ route('admin.marketing.templates.store') }}" method="POST">
            @csrf
            <label for="name">Template Name</label>
            <input type="text" id="name" name="name" placeholder="Enter template name" required>

            <label for="content">Template Content (Markdown)</label>
            <textarea id="content" name="content" placeholder="Enter markdown content here..." required></textarea>

            <button type="submit" class="save-btn">Save Template</button>
        </form>

        <div class="preview-container">
            <h2>Email Preview</h2>
            <div id="preview" class="preview-content">
                <p>Start typing your email content to see the live preview here...</p>
            </div>
        </div>
    </div>

    <!-- Markdown Rendering Library -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const contentInput = document.getElementById('content');
            const preview = document.getElementById('preview');

            if (typeof marked !== 'undefined') {
                contentInput.addEventListener('input', () => {
                    const markdown = contentInput.value;
                    preview.innerHTML = marked(markdown);
                });
            } else {
                console.error('Marked.js is not loaded correctly.');
            }
        });
    </script>
</body>
</html>
