<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App</title>
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style1.css') ?>">
</head>
<body>
<div id="container">
    <aside>
        <header>
            <input type="text" placeholder="search">
        </header>
        <ul id="users">
            <?php foreach($user as $index => $user){ ?>
              <?php if( $user['name']!=session()->get('user_name')):?>
            <li id="user_<?php echo $index; ?>" onclick="startChat('<?php echo $user['name']; ?>')">
           
                <img src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/1940306/chat_avatar_01.jpg" alt="">
                <div>
                    <h2>
                      <?php echo $user['name']; ?></h2>
                     
                    <h3>
                        <span class="status green"></span>
                        online
                    </h3>
                </div>
               
            </li>
            <?php endif?>
            <?php } ?>
        </ul>
    </aside>
    <main>
        <header>
            <img src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/1940306/chat_avatar_01.jpg" alt="">
            <div>
                <h2>Chat with <span id="chatWith">Select a user to start chatting</span></h2>
                
            </div>
            <img src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/1940306/ico_star.png" alt="">
        </header>
        <ul id="chat">
            <!-- Messages will be dynamically appended here -->
        </ul>
        <footer>
            <textarea id="textarea" cols="30" rows="1" placeholder="Write a message..."></textarea>
            <!-- <button id="sendbtn" style="width: 100px; margin: 10px; background-color: #75f569; font-weight: bold; padding: 0px 5px; border-radius: 30px; border: none">Send</button> -->
            <a href="" id="sendbtn">Send</a>
            </footer>
    </main>
</div>

<script src="<?php echo base_url('assets/cdn/socket.js') ?>" crossorigin="anonymous"></script>
<script>
    const socket = io('http://localhost:3001');
let name = "<?php echo session()->get('user_name'); ?>";
let textarea = document.querySelector('#textarea');
let messageArea = document.querySelector('#chat');
let sendBtn = document.getElementById('sendbtn');
let currentReceiver = null;

socket.emit('join', name);

textarea.addEventListener('keyup', (e) => {
  if (e.key === 'Enter' && currentReceiver) {
    sendMessage(e.target.value);
  }
});

sendBtn.addEventListener('click', () => {
  if (textarea.value && currentReceiver) {
    sendMessage(textarea.value);
  }
});

function startChat(receiver) {
  currentReceiver = receiver;
  document.getElementById('chatWith').innerText = receiver;
  loadPastMessages(receiver);
}

function sendMessage(message) {
  let msg = {
    sender: name,
    receiver: currentReceiver,
    message: message.trim(),
    timeStamp: new Date().toLocaleTimeString()
  };
  appendMessage(msg, 'me');
  textarea.value = '';
  scrollToBottom();
  socket.emit('privateMessage', msg);
}

function appendMessage(msg, type) {
  let mainDiv = document.createElement('li');
  mainDiv.classList.add(type);

  let markup = `
    <div class="entete">
        <span class="status ${type === 'me' ? 'blue' : 'green'}"></span>
        <h2>${msg.sender}</h2>
        <h3>${msg.timeStamp}</h3>
    </div>
    <div class="triangle"></div>
    <div class="message">
        ${msg.message}
    </div>
  `;
  mainDiv.innerHTML = markup;
  messageArea.appendChild(mainDiv);
}

socket.on('privateMessage', (msg) => {
  if (msg.sender === currentReceiver || msg.receiver === name) {
    appendMessage(msg, 'you');
    scrollToBottom();
  }
});

function loadPastMessages(receiver) {
  // Clear previous messages
  messageArea.innerHTML = '';
  // Load past messages from the server for this particular chat
  socket.emit('loadPastMessages', {sender: name, receiver: receiver});
}

socket.on('pastMessages', (messages) => {
  messages.forEach((msg) => {
    if (msg.sender === name) {
      appendMessage(msg, 'me');
    } else {
      appendMessage(msg, 'you');
    }
  });
  scrollToBottom();
});

function scrollToBottom() {
  messageArea.scrollTop = messageArea.scrollHeight;
}

// Automatically start chat with the first user when the page loads
window.onload = function() {
  let firstUser = document.querySelector('#users li');
  if (firstUser) {
    firstUser.click();
  }

}
</script>
</body>
</html>
