<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Templates</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
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
        form textarea {
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
        .test-mail-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: linear-gradient(90deg, #ff512f, #dd2476);
            color: #fff;
            text-transform: uppercase;
            font-weight: 600;
            border-radius: 30px;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
        }
        .test-mail-btn:hover {
            transform: translateY(-3px);
            background: linear-gradient(90deg, #dd2476, #ff512f);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Email Template</h1>
        <form>
            <label for="content">Template Content (Markdown)</label>
            <textarea id="content" placeholder="Enter markdown content here..."></textarea>
        </form>

        <div class="preview-container">
            <h2>Preview</h2>
            <div id="preview" class="preview-content">
                Start typing your content to see the preview...
            </div>
        </div>

        <!-- Test Mail Button -->
        <button class="test-mail-btn" id="sendTestMail">Send Test Mail</button>
    </div>

    <!-- Include Marked.js -->
    <script src="https://cdn.jsdelivr.net/npm/marked@4.3.0/marked.min.js"></script>

    <!-- Custom Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const contentInput = document.getElementById('content');
            const preview = document.getElementById('preview');
            const testMailButton = document.getElementById('sendTestMail');

            // Markdown Preview
            if (contentInput && typeof marked.parse === 'function') {
                contentInput.addEventListener('input', () => {
                    const markdown = contentInput.value;
                    preview.innerHTML = marked.parse(markdown);
                });
            } else {
                console.error('Content input or marked library is not available.');
            }

            // Test Mail Trigger
            testMailButton.addEventListener('click', async () => {
                const content = contentInput.value; // Grab Markdown content
                const response = await fetch('/admin/marketing/templates/send-test-mail', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' // Ensure CSRF Token is included
                    },
                    body: JSON.stringify({
                        email: 'peverelli.t@gmail.com',
                        content
                    })
                });

                if (response.ok) {
                    alert('Test mail sent successfully!');
                } else {
                    alert('Failed to send test mail.');
                }
            });
        });
    </script>
</body>
</html>
