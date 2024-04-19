<?php
	use Aws\BedrockRuntime\BedrockRuntimeClient;

    $query = $_POST["query"];
    $style_preset = $_POST["style"];
    //print_r($query);

    if(!$query){
        $query = "A grassy knoll";
    }

    if(!$style_preset){
        $style_preset = "";
    }

	$bedrockRuntimeClient = new BedrockRuntimeClient([
		'region' => 'us-east-1',
		'version' => 'latest',
		//'profile' => $profile,
		'credentials' => [
			'key'    => AWS_ACCESS_KEY,
			'secret' => AWS_SECRET_KEY,
		],
	]);

	//$image_prompt = 'Create an image of an intergalactic villain\'s lair filled with advanced technology and multiple screens displaying different landscapes of Earth, including forests, cities, and oceans. Include a large red button on a central console.';
	$seed = rand(0, 2147483647);
	//$base64 = invokeTitanImage($image_prompt, $titanSeed);
	$base64_image_data = "";

	try {
		$modelId = 'stability.stable-diffusion-xl-v1';

        // StableDiffusion params doc: https://platform.stability.ai/docs/api-reference#tag/Text-to-Image/operation/textToImage
        $body = [
            'text_prompts' => [
                ['text' => $query]
            ],
            'seed' => $seed,
            'cfg_scale' => 10,
            'steps' => 30,
            'height' => 512,
            'width' => 512
        ];

        if ($style_preset) {
            $body['style_preset'] = $style_preset;
        }

        $result = $bedrockRuntimeClient->invokeModel([
            'contentType' => 'application/json',
            'body' => json_encode($body),
            'modelId' => $modelId,
        ]);

        $response_body = json_decode($result['body']);

		//print_r($response_body);

		//$base64_image_data = $response_body->images[0];
        $base64_image_data = $response_body->artifacts[0]->base64;

		$saveDir = 'backgrounds';
        $output_dir = '../assets/' . $saveDir . '-full';
		$absolute_path = '/assets/' . $saveDir . '-full';

        if (!file_exists($output_dir)) {
            mkdir($output_dir);
        }

        $i = 1;
        while (file_exists("$output_dir/$modelId" . '_' . "$i.png")) {
            $i++;
        }

        $image_data = base64_decode($base64_image_data);

		// TODO: Send image as url encoded base64 and modify the save script to handle.
        $image_path = "$output_dir/$modelId" . '_' . "$i.png";

        $file = fopen($image_path, 'wb');
        fwrite($file, $image_data);
        fclose($file);

        $responseObj = new stdClass;

        $responseObj->url = "$absolute_path/$modelId" . '_' . "$i.png";;
		// // Pass the image as a url encoded base64 string.
        // $responseObj->url = "data:image/png;base64,".$base64_image_data;

        $output->data = array($responseObj);

	} catch (Exception $e) {
		echo "Error: ({$e->getCode()}) - {$e->getMessage()}\n";
	}

	//$output->data = $base64_image_data;

	// Record the model that was used
	$output->model = $modelId;
?>