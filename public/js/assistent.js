let conversation_id = null;


async function sendMessage() {
    let systemInput = document.getElementById('systemInput').value;
    let userInput = document.getElementById('userInput').value;
    let conversation = document.getElementById('chatBox')
    if (userInput.trim() !== '') {
        let chatBox = document.getElementById('chatBox');
        let userMessage = document.createElement('div');
        userMessage.className = 'conversion'
        userMessage.textContent = 'User: ' + userInput;
        chatBox.appendChild(userMessage);
        
        let aiMessage = document.createElement('div');
        aiMessage.className = 'conversion';
        aiMessage.textContent = 'AI: ' + await getAIResponse(systemInput, conversation);
        chatBox.appendChild(aiMessage);

        document.getElementById('userInput').value = '';
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}

async function getAIResponse(system, messages) {

    messages = messages.getElementsByClassName('conversion')
    const conversation = []; 

    for (let message of messages) {
        message = (message.textContent).split(':');
        let $site = message.shift();

        if($site === 'User'){
            conversation.push({'User': message[0]})
        }else if ($site === 'AI') {
            conversation.push({'AI': message[0]})
        } else {
            console.error($site)
        }
    }
    return await askChat(system, conversation)
}

async function askChat(system, conversation) {
    const data = {
        'id': conversation_id,
        'system': system,
        'conversation': conversation
    }
    console.log(JSON.stringify(data))

    try {
        const response = await axios.post(dataUrl, data)
        conversation_id = response.data.id
        return response.data.answer

    } catch (error) {
        console.error(error)
        return null
    }
   
}

