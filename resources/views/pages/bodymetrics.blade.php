<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('logo.jpg') }}" />
    <title>FitScan Setup</title>
    <link rel="stylesheet" href="{{ asset('css/metrics.css') }}">
</head>

<body>

    <div class="container">
        <div class="step active" id="step1">
            <h2>Identify Your Gender</h2>
            <div class="option" onclick="selectOption(this, 'gender', 'Male')">♂ Male</div>
            <div class="option" onclick="selectOption(this, 'gender', 'Female')">♀ Female</div>
            <button class="btn" onclick="nextStep(2)">Continue</button>
        </div>

        <div class="step" id="step2">
            <h2>Enter Your Info</h2>
            <input type="number" id="age" placeholder="Age">
            <input type="number" id="height" placeholder="Height (cm)">
            <input type="number" id="weight" placeholder="Weight (kg)">
            <button class="btn" onclick="nextStep(3)">Continue</button>
            <button class="btn btn-back" onclick="prevStep(1)">Back</button>
        </div>

        <div class="step" id="step3">
            <h2>Where do you train?</h2>
            <div class="option" onclick="selectOption(this, 'training', 'Gym')">🏋️ Gym</div>
            <div class="option" onclick="selectOption(this, 'training', 'Home')">🏠 Home</div>
            <button class="btn" onclick="nextStep(4)">Continue</button>
            <button class="btn btn-back" onclick="prevStep(2)">Back</button>
        </div>

        <div class="step" id="step4">
            <h2>What is Your Goal?</h2>
            <div class="option" onclick="selectOption(this, 'goal', 'Weight Loss')">🔥 Weight Loss</div>
            <div class="option" onclick="selectOption(this, 'goal', 'Muscle Gain')">💪 Muscle Gain</div>
            <button class="btn" onclick="nextStep(5)">Continue</button>
            <button class="btn btn-back" onclick="prevStep(3)">Back</button>
        </div>

        <div class="step" id="step5">
            <h2>Upload Your InBody Scan</h2>
            <input type="file" id="inbodyImage">
            <button class="btn" onclick="submitData()">Submit</button>
            <button class="btn btn-back" onclick="prevStep(4)">Back</button>
        </div>
    </div>

    <script>
        let currentStep = 1;
        let userData = {};

        function nextStep(step) {
            document.getElementById(`step${currentStep}`).classList.remove("active");
            document.getElementById(`step${step}`).classList.add("active");
            currentStep = step;
        }

        function prevStep(step) {
            document.getElementById(`step${currentStep}`).classList.remove("active");
            document.getElementById(`step${step}`).classList.add("active");
            currentStep = step;
        }

        function selectOption(element, key, value) {
            document.querySelectorAll(`#step${currentStep} .option`).forEach(e => e.classList.remove("selected"));
            element.classList.add("selected");
            userData[key] = value;
        }

        function submitData() {
            userData.age = document.getElementById("age").value;
            userData.height = document.getElementById("height").value;
            userData.weight = document.getElementById("weight").value;
            let inbodyImage = document.getElementById("inbodyImage").files[0];

            if (!userData.gender || !userData.age || !userData.height || !userData.weight || !userData.training || !userData
                .goal || !inbodyImage) {
                alert("Please complete all fields!");
                return;
            }

            alert("Data submitted successfully! 🎉");
            console.log(userData);
        }
    </script>

</body>

</html>
