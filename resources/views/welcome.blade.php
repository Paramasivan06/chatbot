<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel 10 - BotMan Chatbot - Code Shotcut</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            margin: 0;
            padding: 0;
        }
        
        #chat-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 350px;
            height: 500px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 40px rgba(0,0,0,0.16);
            display: flex;
            flex-direction: column;
            background: #fff;
            z-index: 1000;
        }
        
        #chat-header {
            padding: 15px;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            color: white;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        #chat-close {
            cursor: pointer;
            font-size: 20px;
        }
        
        #chat-messages {
            flex-grow: 1;
            padding: 15px;
            overflow-y: auto;
            background: #f5f8fb;
        }
        
        .message {
            margin-bottom: 15px;
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 18px;
            clear: both;
            word-wrap: break-word;
        }
        
        .bot-message {
            background: white;
            color: #4a4a4a;
            border: 1px solid #e6e6e6;
            float: left;
        }
        
        .user-message {
            background: #6e8efb;
            color: white;
            float: right;
        }
        
        #chat-input-container {
            display: flex;
            padding: 10px;
            border-top: 1px solid #ddd;
        }
        
        #chat-input {
            flex-grow: 1;
            border: none;
            outline: none;
            padding: 10px;
            border-radius: 20px;
            background: #f0f0f0;
        }
        
        #chat-send {
            border: none;
            padding: 10px 15px;
            margin-left: 10px;
            background: #6e8efb;
            color: white;
            border-radius: 20px;
            cursor: pointer;
        }
        
        #chat-bubble {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 5px 40px rgba(0,0,0,0.16);
            z-index: 9999;
        }
        
        #chat-bubble img {
            width: 30px;
            height: 30px;
        }
        
        .hidden {
            display: none !important;
        }
        
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <!-- Chat bubble -->
    <div id="chat-bubble">
        <svg fill="#ffffff" width="24" height="24" viewBox="0 0 24 24">
            <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
        </svg>
    </div>
    
    <!-- Chat container -->
    <div id="chat-container" class="hidden">
        <div id="chat-header">
            <span>Code Shotcut Assistant</span>
            <span id="chat-close">×</span>
        </div>
        <div id="chat-messages">
            <div class="message bot-message">WELCOME TO CODE SHOTCUT</div>
        </div>
        <div id="chat-input-container">
            <input type="text" id="chat-input" placeholder="Type your message here...">
            <button id="chat-send">Send</button>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Set up CSRF token for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            // Toggle chat window
            $('#chat-bubble').click(function() {
                $('#chat-bubble').addClass('hidden');
                $('#chat-container').removeClass('hidden');
            });
            
            // Close chat window
            $('#chat-close').click(function() {
                $('#chat-container').addClass('hidden');
                $('#chat-bubble').removeClass('hidden');
            });
            
            // Send message
            $('#chat-send').click(sendMessage);
            $('#chat-input').keypress(function(e) {
                if(e.which == 13) {
                    sendMessage();
                }
            });
            
            function sendMessage() {
                let message = $('#chat-input').val().trim();
                if(message) {
                    // Add user message to chat
                    $('#chat-messages').append('<div class="message user-message">' + message + '</div>');
                    $('#chat-messages').append('<div class="clearfix"></div>');
                    $('#chat-input').val('');
                    
                    // Scroll to bottom
                    $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
                    
                    // Send to BotMan
                    $.ajax({
                        url: '/botman',
                        type: 'POST',
                        data: {
                            message: message
                        },
                        success: function(response) {
                            // Add bot response to chat
                            if(response && response.message) {
                                $('#chat-messages').append('<div class="message bot-message">' + response.message + '</div>');
                                $('#chat-messages').append('<div class="clearfix"></div>');
                                $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            $('#chat-messages').append('<div class="message bot-message">Sorry, there was an error processing your request.</div>');
                            $('#chat-messages').append('<div class="clearfix"></div>');
                            $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>