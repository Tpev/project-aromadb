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

        h1, h2 {
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
            color: #fff;
        }

        h1::after, h2::after {
            content: '';
            display: block;
            width: 120px;
            height: 3px;
            margin: 10px auto 0;
            background: linear-gradient(90deg, #ff512f, #dd2476);
            border-radius: 2px;
        }

        /* Button Styles */
        .upload-btn, .save-btn {
            display: inline-block;
            margin: 20px auto;
            padding: 12px 30px;
            text-transform: uppercase;
            background: linear-gradient(90deg, #ff512f, #dd2476);
            color: #fff;
            font-weight: 600;
            border-radius: 30px;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 5px 15px rgba(255, 81, 47, 0.5);
        }

        .upload-btn:hover, .save-btn:hover {
            transform: translateY(-3px) scale(1.05);
            background: linear-gradient(90deg, #dd2476, #ff512f);
            box-shadow: 0 8px 20px rgba(255, 81, 47, 0.7);
        }

        /* Table Styling */
        .table-responsive {
            margin: 30px auto;
            background: rgba(58, 58, 79, 0.9);
            padding: 15px;
            border-radius: 15px;
            overflow-x: auto;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            color: #f0f0f0;
        }

        thead {
            background: rgba(58, 58, 79, 0.95);
        }

        th, td {
            padding: 12px;
            text-align: left;
            font-size: 0.9rem;
        }

        th {
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #ddd;
        }

        tbody tr {
            transition: background 0.2s, transform 0.2s;
            border-bottom: 1px solid rgba(58, 58, 79, 0.8);
        }

        tbody tr:hover {
            background: rgba(58, 58, 79, 0.8);
            transform: translateX(3px);
        }

        /* Form Styling */
        form {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: rgba(58, 58, 79, 0.9);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }

        form label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #ddd;
            font-size: 1rem;
        }

        form input, form textarea {
            width: 100%;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: none;
            outline: none;
            font-size: 1rem;
            background: rgba(42, 42, 60, 0.9);
            color: #fff;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
        }

        form textarea {
            resize: vertical;
            min-height: 150px;
        }

        form input:focus, form textarea:focus {
            box-shadow: 0 0 10px rgba(255, 81, 47, 0.5);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            h1, h2 {
                font-size: 1.8rem;
            }

            th, td {
                font-size: 0.8rem;
            }

            form {
                padding: 10px;
            }

            form label {
                font-size: 0.9rem;
            }

            form input, form textarea {
                font-size: 0.8rem;
            }
        }
		        /* Preview Pane */
        .preview-container {
            margin-top: 20px;
            background: rgba(58, 58, 79, 0.9);
            padding: 15px;
            border-radius: 12px;
            color: #f0f0f0;
            font-size: 1rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .preview-container h2 {
            margin-bottom: 10px;
            text-align: center;
            font-size: 1.5rem;
            color: #fff;
        }

        .preview-content {
            padding: 10px;
            background: rgba(42, 42, 60, 0.8);
            border-radius: 8px;
            overflow: auto;
            max-height: 300px;
            font-size: 1rem;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Page Title -->
        <h1>Email Templates</h1>
        
        <!-- Create Button -->
        <a href="#create-template" class="upload-btn">Create New Template</a>

        <!-- Templates Table -->
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $template)
                        <tr>
                            <td>{{ $template->name }}</td>
                            <td>
                                <a href="{{ route('admin.marketing.templates.edit', $template->id) }}" class="save-btn">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Form -->
        <form id="template-form" action="{{ route('admin.marketing.templates.store') }}" method="POST">
            @csrf
            <label for="name">Template Name</label>
            <input type="text" id="name" name="name" placeholder="Enter template name" required>

            <label for="content">Template Content (Markdown)</label>
            <textarea id="content" name="content" placeholder="Enter markdown content here..." required></textarea>

            <button type="submit" class="save-btn">Save Template</button>
        </form>

        <!-- Live Preview -->
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
        // Live Preview for Markdown Content
        const contentInput = document.getElementById('content');
        const preview = document.getElementById('preview');

        contentInput.addEventListener('input', () => {
            const markdown = contentInput.value;
            preview.innerHTML = marked(markdown);
        });
    </script>
    </div>
</body>
</html>
