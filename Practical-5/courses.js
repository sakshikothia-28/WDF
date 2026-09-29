let courses = [];
let filteredCourses = [];

let currentPage = 1;
const coursesPerPage = 6;

// Wait until HTML is completely loaded
document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("courseSearch");
    const categoryFilter = document.getElementById("categoryFilter");
    const sortCourses = document.getElementById("sortCourses");
    // Load courses from JSON
    fetch("../Data/courses.json")
        .then(response => {
            if (!response.ok) {
                throw new Error("Failed to load course data");
            }
            return response.json();
        })
        .then(data => {
            courses = data;
            filteredCourses = [...courses];
            // Hide loading message
            document.getElementById("loadingMessage").style.display = "none";
            // Create category options
            loadCategories();
            // Display courses
            renderCourses();
        })
        .catch(error => {
            console.error(error);
            document.getElementById("loadingMessage").style.display = "none";
            document.getElementById("errorMessage").textContent =
                "Unable to load courses. Please try again later.";
        });
    // SEARCH
    searchInput.addEventListener("input", applyFilters);
    // CATEGORY FILTER
    categoryFilter.addEventListener("change", applyFilters);
    // SORT
    sortCourses.addEventListener("change", applyFilters);

});
// --------------------------------------------------
// LOAD CATEGORIES INTO DROPDOWN
// --------------------------------------------------
function loadCategories() {
    const categoryFilter = document.getElementById("categoryFilter");
    const categories = [...new Set(courses.map(course => course.category)
    )];
    categories.forEach(category => {
        const option = document.createElement("option");
        option.value = category;
        option.textContent = category;
        categoryFilter.appendChild(option);
    });
}
// --------------------------------------------------
// SEARCH + FILTER + SORT
// --------------------------------------------------
function applyFilters() {
    const searchText = document.getElementById("courseSearch")
            .value
            .toLowerCase();
    const selectedCategory = document.getElementById("categoryFilter").value;
    const selectedSort = document.getElementById("sortCourses").value;
    // SEARCH + CATEGORY FILTER
    filteredCourses = courses.filter(course => {
        const matchesSearch =
            course.name.toLowerCase().includes(searchText) ||
            course.instructor.toLowerCase().includes(searchText) ||
            course.category.toLowerCase().includes(searchText);
        const matchesCategory = selectedCategory === "all" || course.category === selectedCategory;
        return matchesSearch && matchesCategory;
    });
    // SORT COURSES
    if (selectedSort === "az") {
        filteredCourses.sort((a, b) => a.name.localeCompare(b.name)
        );
    } else if (selectedSort === "za") {
        filteredCourses.sort((a, b) => b.name.localeCompare(a.name)
        );
    } else if (selectedSort === "semesterLow") {
        filteredCourses.sort((a, b) => a.semester - b.semester
        );
    } else if (selectedSort === "semesterHigh") {
        filteredCourses.sort((a, b) => b.semester - a.semester
        );
    }
    // Start from first page
    currentPage = 1;
    renderCourses();
}
// --------------------------------------------------
// DISPLAY COURSES
// --------------------------------------------------
function renderCourses() {
    const container = document.getElementById("courseContainer");
    container.innerHTML = "";
    // If no courses found
    if (filteredCourses.length === 0) {
        container.innerHTML = "<p>No courses found.</p>";
        document.getElementById("pagination").innerHTML = "";
        return;
    }
    // Calculate pagination
    const startIndex = (currentPage - 1) * coursesPerPage;
    const endIndex = startIndex + coursesPerPage;
    const coursesToDisplay = filteredCourses.slice(startIndex, endIndex);
    // Create course cards
    coursesToDisplay.forEach(course => {
        const card = document.createElement("div");
        card.className = "course-card";
        card.innerHTML = `
            <h3>${course.name}</h3>
            <p>
                <strong>Category:</strong>
                ${course.category}
            </p>
            <p>
                <strong>Semester:</strong>
                ${course.semester}
            </p>
            <p>
                <strong>Instructor:</strong>
                ${course.instructor}
            </p>
            <p>
                <strong>Duration:</strong>
                ${course.duration}
            </p>
        `;
        container.appendChild(card);
    });
    // Display pagination buttons
    createPagination();
}
// --------------------------------------------------
// PAGINATION
// --------------------------------------------------
function createPagination() {
    const pagination = document.getElementById("pagination");
    pagination.innerHTML = "";
    const totalPages = Math.ceil(filteredCourses.length / coursesPerPage);
    // Previous button
    if (currentPage > 1) {
        const previousButton = document.createElement("button");
        previousButton.textContent = "Previous";
        previousButton.onclick = () => {
            currentPage--;
            renderCourses();
        };
        pagination.appendChild(previousButton);
    }
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        const pageButton = document.createElement("button");
        pageButton.textContent = i;
        pageButton.onclick = () => {
            currentPage = i;
            renderCourses();
        };
        pagination.appendChild(pageButton);
    }
    // Next button
    if (currentPage < totalPages) {
        const nextButton = document.createElement("button");
        nextButton.textContent = "Next";
        nextButton.onclick = () => {
            currentPage++;
            renderCourses();
        };
        pagination.appendChild(nextButton);
    }
}