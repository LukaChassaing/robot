<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="assets/css/general.css" rel="stylesheet">


    <script src="assets/js/face-api.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="assets/js/webcam.js"></script>
</head>

<body>
    <img id="fluxWebcam" src="" />
    <img id="imageReferenceLuka" class="imageDeReference" src="luka.png" />
    <img id="imageReferenceDora" class="imageDeReference" src="dora.png" />
</body>

<script>
    socket = new WebSocket("ws://<?php echo $_SERVER['HTTP_HOST'] ?>:12345/");
    personnesDetectees = [];
    socket.onmessage = function(msg) {
        document.getElementById("time").innerText = msg.data;
    };
    socket.onopen = function(e) {
        console.log("[open] Connection established");
        console.log("Sending to server");
        socket.send("My name is John");
    };
    $(function() {

        const webcam = new Webcam("http://<?php echo $_SERVER['HTTP_HOST'] ?>:8085/?action=snapshot", $("#fluxWebcam"));

        const path = "/robot/neuronne-detection-reconnaissance-faciale/models";
        Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(path),
            faceapi.nets.faceLandmark68Net.loadFromUri(path),
            faceapi.nets.faceRecognitionNet.loadFromUri(path),
            faceapi.nets.faceExpressionNet.loadFromUri(path),
            faceapi.nets.ageGenderNet.loadFromUri(path),
            faceapi.nets.ssdMobilenetv1.loadFromUri(path + '/ssd_mobilenetv1')
        ]).then(function() {
            webcam.createImageLayer()
                .then(function() {
                    const video = document.getElementById('fluxWebcam')
                    const imageReferenceLuka = document.getElementById('imageReferenceLuka')
                    const imageReferenceDora = document.getElementById('imageReferenceDora')

                    const canvasReferenceLuka = faceapi.createCanvasFromMedia(imageReferenceLuka)
                    const canvasReferenceDora = faceapi.createCanvasFromMedia(imageReferenceDora)
                    const canvas = faceapi.createCanvasFromMedia(video)

                    canvasReferenceLuka.classList.add("canvas-dis-none");
                    canvasReferenceDora.classList.add("canvas-dis-none");

                    document.body.append(canvasReferenceLuka)
                    document.body.append(canvasReferenceDora)
                    document.body.append(canvas)

                    const displaySize = {
                        width: $("#fluxWebcam").width(),
                        height: $("#fluxWebcam").height()
                    }

                    faceapi.matchDimensions(canvas, displaySize)

                    setInterval(async () => {
                        webcam.createImageLayer();

                        const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceExpressions().withAgeAndGender().withFaceDescriptors()
                        const lukaDescriptor = await faceapi.detectAllFaces(canvasReferenceLuka).withFaceLandmarks().withFaceDescriptors()
                        const doraDescriptor = await faceapi.detectAllFaces(canvasReferenceDora).withFaceLandmarks().withFaceDescriptors()

                        const labeledDescriptors = [
                            new faceapi.LabeledFaceDescriptors(
                                'Luka',
                                [
                                    new Float32Array(lukaDescriptor[0].descriptor)
                                ]
                            ),
                            new faceapi.LabeledFaceDescriptors(
                                'Dora',
                                [
                                    new Float32Array(doraDescriptor[0].descriptor)
                                ]
                            ),
                        ]

                        if (!lukaDescriptor.length) {
                            return
                        }

                        if (!doraDescriptor.length) {
                            return
                        }

                        const faceMatcher = new faceapi.FaceMatcher(labeledDescriptors)

                        detections.forEach(detection => {
                            bestMatch = faceMatcher.findBestMatch(detection.descriptor)
                        })

                        const resizedDetections = faceapi.resizeResults(detections, displaySize)
                        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height)
                        faceapi.draw.drawDetections(canvas, resizedDetections)
                        faceapi.draw.drawFaceLandmarks(canvas, resizedDetections)
                        faceapi.draw.drawFaceExpressions(canvas, resizedDetections)

                        if (typeof resizedDetections[0] !== 'undefined') {
                            console.log(resizedDetections)
                            if (bestMatch._label == "unknown") {
                                console.log("inconnue")
                                text = [
                                    "Personne inconnue"
                                ]
                                // socket.send(JSON.stringify({
                                //     'type': 1,
                                //     'nomPersonneDetectee': "Personne inconnue"
                                // }))
                            } else {
                                text = [
                                    bestMatch._label
                                ]
                                if(socket.readyState === 1){
                                    socket.send(JSON.stringify({
                                        'type': 1,
                                        'nomPersonneDetectee': bestMatch._label
                                    }))
                                }
                                    
                                else{
                                    console.log('socket deconnecté')
                                    socket = new WebSocket("ws://<?php echo $_SERVER['HTTP_HOST'] ?>:12345/");
                                }

                                console.log(bestMatch._label + " detecté(e) !")

                            }

                            const anchor = {
                                x: resizedDetections[0].alignedRect._box._x,
                                y: resizedDetections[0].alignedRect._box._y
                            }
                            // see DrawTextField below
                            const drawOptions = {
                                anchorPosition: 'TOP_LEFT',
                                backgroundColor: 'rgba(0, 0, 0, 0.5)'
                            }
                            const drawBox = new faceapi.draw.DrawTextField(text, anchor, drawOptions)
                            drawBox.draw(canvas)
                        }

                    }, 150)

                })
        })
    });
</script>

</html>