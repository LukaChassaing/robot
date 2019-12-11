<script src="tfjs@0.12.7"> </script>
<script src="coco-ssd@0.1.1"> </script>
<script src="jquery-3.4.1.min.js"></script>
<script src="webcam.js"></script>

<img id="fluxWebcam" src="http://<?php echo $_SERVER['HTTP_HOST'] ?>:8085/?action=snapshot" crossorigin="anonymous" />

<script>
    $(function() {
        cocoSsd.load().then((model) => {
            cocossdModel = model;
            const webcam = new Webcam("http://<?php echo $_SERVER['HTTP_HOST'] ?>:8085/?action=snapshot", $("#fluxWebcam"));
            setInterval(() => {
                webcam.createImageLayer()
                const img = document.getElementById('fluxWebcam');
                model.detect(img).then(predictions => {
                    console.log('Predictions: ', predictions);

                });
            }, 40);
        });
    })
</script>