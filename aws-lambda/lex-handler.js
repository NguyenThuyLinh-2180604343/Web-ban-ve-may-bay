// AWS Lambda function for Lex integration
const AWS = require('aws-sdk');

const lex = new AWS.LexRuntimeV2();
const polly = new AWS.Polly();
const bedrock = new AWS.BedrockRuntime();

exports.handler = async (event) => {
    try {
        const { action, input, sessionId } = JSON.parse(event.body);
        
        switch (action) {
            case 'chat':
                return await handleChatRequest(input, sessionId);
            case 'voice':
                return await handleVoiceRequest(input, sessionId);
            default:
                return {
                    statusCode: 400,
                    body: JSON.stringify({ error: 'Invalid action' })
                };
        }
    } catch (error) {
        console.error('Error:', error);
        return {
            statusCode: 500,
            body: JSON.stringify({ error: 'Internal server error' })
        };
    }
};

async function handleChatRequest(input, sessionId) {
    // Process with Lex V2
    const lexParams = {
        botId: process.env.LEX_BOT_ID,
        botAliasId: process.env.LEX_BOT_ALIAS_ID,
        localeId: 'vi_VN',
        sessionId: sessionId,
        text: input
    };
    
    const lexResponse = await lex.recognizeText(lexParams).promise();
    
    // Enhance response with Bedrock if needed
    let enhancedResponse = lexResponse.messages[0].content;
    
    if (lexResponse.intent?.name === 'ComplexQuery') {
        enhancedResponse = await enhanceWithBedrock(input);
    }
    
    // Generate speech with Polly
    const audioUrl = await generateSpeech(enhancedResponse);
    
    return {
        statusCode: 200,
        headers: {
            'Access-Control-Allow-Origin': '*',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            text: enhancedResponse,
            audioUrl: audioUrl,
            intent: lexResponse.intent?.name,
            sessionState: lexResponse.sessionState
        })
    };
}

async function handleVoiceRequest(audioData, sessionId) {
    // TODO: Implement voice processing with Transcribe
    // Convert speech to text, then process with Lex
    return await handleChatRequest(audioData, sessionId);
}

async function enhanceWithBedrock(prompt) {
    const bedrockParams = {
        modelId: 'anthropic.claude-v2',
        contentType: 'application/json',
        accept: 'application/json',
        body: JSON.stringify({
            prompt: `Human: Bạn là trợ lý AI cho website đặt vé máy bay. Trả lời câu hỏi sau bằng tiếng Việt một cách thân thiện và hữu ích: ${prompt}\n\nAssistant:`,
            max_tokens_to_sample: 300,
            temperature: 0.7
        })
    };
    
    const response = await bedrock.invokeModel(bedrockParams).promise();
    const responseBody = JSON.parse(response.body.toString());
    
    return responseBody.completion.trim();
}

async function generateSpeech(text) {
    const pollyParams = {
        Text: text,
        OutputFormat: 'mp3',
        VoiceId: 'Linh', // Vietnamese voice
        LanguageCode: 'vi-VN'
    };
    
    const pollyResponse = await polly.synthesizeSpeech(pollyParams).promise();
    
    // Upload to S3 and return URL (implement based on your setup)
    // For now, return base64 audio data
    return `data:audio/mp3;base64,${pollyResponse.AudioStream.toString('base64')}`;
}