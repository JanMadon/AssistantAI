function sendMessage() {
    let userInput = document.getElementById('userInput').value;
    let conversation = document.getElementById('chatBox')
    if (userInput.trim() !== '') {
        let chatBox = document.getElementById('chatBox');
        let userMessage = document.createElement('div');
        userMessage.className = 'conversion'
        userMessage.textContent = 'User: ' + userInput;
        chatBox.appendChild(userMessage);

        console.log(getAIResponse(conversation));
        
        // Here you would typically send the message to the server and get a response
        let aiMessage = document.createElement('div');
        aiMessage.className = 'conversion';
        aiMessage.textContent = 'AI: ' + getAIResponse(conversation);
        chatBox.appendChild(aiMessage);

        document.getElementById('userInput').value = '';
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}

function getAIResponse(messages) {
    messages = messages.getElementsByClassName('conversion')
    const conversation = []; 

    for (let message of messages) {
        message = (message.textContent).split(':');
        let $site = message.shift();

        console.log($site);
        if($site === 'User'){
            conversation.push({'User': message})
        }else if ($site === 'AI') {
            conversation.push({'AI': message})
        } else {
            console.error($site)
        }
    }

    return askChat(conversation)
}

async function askChat(conversation) {
   
    try {
        const response = await axios.post(dataUrl, conversation)
        console.log(response);
        return response.data
    } catch (error) {
        console.error(error)
        return null
    }
   
}

