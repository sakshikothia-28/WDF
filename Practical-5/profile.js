fetch("../Data/profile.json")
    .then(response => {
        if (!response.ok) {
            throw new Error("Failed to load profile data");
        }
        return response.json();
    })
    .then(profile => {
        document.getElementById("studentId").textContent = profile.studentId;
        document.getElementById("name").textContent = profile.name;
        document.getElementById("gender").textContent = profile.gender;
        document.getElementById("age").textContent = profile.age;
        document.getElementById("dob").textContent = profile.dob;
        document.getElementById("semester").textContent = profile.semester;
        document.getElementById("department").textContent = profile.department;
        document.getElementById("email").textContent = profile.email;
        document.getElementById("contact").textContent = profile.contact;
        document.getElementById("address").textContent = profile.address;
        document.getElementById("profilePhoto").src = profile.photo;
    })
    .catch(error => {
        console.error(error);
        document.getElementById("name").textContent =
            "Unable to load profile";
    });