<?php
$modelId = POSTval("model", "sdf");

$query = $_POST["query"];
if(!$query){
	$query = "A grassy knoll";
}

// Get the prompt
// $prompts = new Prompts();
// if(OUTPUT_DEBUG_DATA) {
//     $output->actionId = $actionId;
//     $output->params = $params;
// }
// $output->prompt = $prompts->generatePrompt($actionId, [$query]);
$output->prompt = $query;


if ($modelId) {
	$model = null;
	switch ($modelId) {
		case "oai":
			$model = new ModelDallE();
			break;
		case "ttn":
			$model = new ModelTitanImage();
			break;
		case "sdf":
			$model = new ModelStableDiffusion();
			break;
	}
	if (!$model) {
		$output->error = "Invalid model id";
	} else {
		// Record the model that was used
		$output->model = $model->modelName;
	
		$response = $model->sendPrompt($output->prompt);
		$output->error = $response->error;
	
		$output->data = $response->data;
		$output->json = $response->json;
	}
}
?>