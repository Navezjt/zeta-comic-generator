<?php
$scriptPrompt = <<<SCRIPT
You are a cartoonist and humorist. Write the script for a three panel comic strip.
In the comic strip our main character, a short green humaniod alien named Alpha Zeta, engages in the following premise: {p0}
Include a detailed scene description and words spoken by the main character.
Output your response as a valid json object in the follwing format:
{
    \"title\": \"\",
    \"panels\": [
        {
            \"scene\": \"\",
            \"dialog\": \"\"
        },
        {
            \"scene\": \"\",
            \"dialog\": \"\"
        },
        {
            \"scene\": \"\",
            \"dialog\": \"\"
        }
    ]
}

The following is a description of each property value for the json object:
`title`: The title of the comic strip. Limit to 50 letters.
`panels` is a 3 element array of objects defining each of the 3 panels in the comic strip.
`scene`: A description of the panel scene, including all characters present.
`dialog`: Words spoken by Alpha Zeta. He is the only character that speaks. Do not label the dialog with a character name. This can be an empty string if the character is not speaking.
SCRIPT;

$backgroundPrompt = <<<BACKGROUND
You are a talented artist who draws background art for animated cartoons.
The following describes three scenes in a cartoon featuring the character Alpha Zeta:
- {p0}
- {p1}
- {p2}
 
For each scene, write a description of the background behind Alpha Zeta.
Include enough detail necessary for an AI image generator to render an image of your description.
Output your response as a valid json object in the follwing format:
{
    descriptions: [
        \"background description 1\",
        \"background description 2\",
        \"background description 3\"
    ]
}
Your descriptions will be written within the following rules:
- Do not exceed 500 characters for each description.
- Describe each scene as it would look if the main character, Alpha Zeta, is not present.
- No characters will speak to each other.
- Do not include any items that contain readable text.
- Do not reference a comic strip panel.
BACKGROUND;

$actionPrompt = <<<ACTION
You are a talented artist who directs animated cartoons.
The following describes three scenes in a cartoon featuring the character Alpha Zeta:
- {p0}
- {p1}
- {p2}
For each of the three scenes choose one word, from the following list, which best describes the action or appearance of the main character:  {p3}.
Output your response as a valid json object in the follwing format:
{
    panels: [
        \"word1\",
        \"word2\",
        \"word3\"
    ]
}
ACTION;

$prompts = new stdClass;
$prompts->script = arrayFromHeredoc($scriptPrompt);
$prompts->background = arrayFromHeredoc($backgroundPrompt);
$prompts->action = arrayFromHeredoc($actionPrompt);

function generatePrompt($instructions, $values) {

    $prompt = "";

    // Loop through each instruction
    foreach ($instructions as $instruction) {
        // Loop through each value in the values array
        foreach ($values as $index => $value) {
            // Replace placeholders like {p0}, {p1}, etc., with corresponding values
            $prompt = $prompt = writePromptLine($prompt, str_replace("{p{$index}}", $value, $instruction));
        }
    }

    return $prompt;
}

function writePromptLine($prompt, $line) {
    if($line == "") return $prompt;
    $newLine = "";
    if($prompt != "") $newLine = "\\n";

    $newLine .= $line;

    return $prompt . $newLine;
}

function arrayFromHeredoc($heredoc) {
    $result = explode("\n", $heredoc);
    foreach ($result as $key => $line) {
        // Remove carriage return and new line characters
        $result[$key] = str_replace(["\r", "\n"], '', $line);
    }
    return $result;
}
?>