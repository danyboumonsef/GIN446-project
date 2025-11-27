const cvForm = document.getElementById("cvForm");
const cvPreview = document.getElementById("cvPreview");
const cvContent = document.getElementById("cvContent");
const printBtn = document.getElementById("printBtn");
const editBtn = document.getElementById("editBtn");

cvForm.addEventListener("submit", function(e){
    e.preventDefault();

    const name = document.getElementById("name").value.trim();
    const title = document.getElementById("title").value.trim();
    const email = document.getElementById("email").value.trim();
    const phone = document.getElementById("phone").value.trim();
    const education = document.getElementById("education").value.trim();
    const projects = document.getElementById("projects").value.trim();
    const techSkills = document.getElementById("techSkills").value.trim();
    const tools = document.getElementById("tools").value.trim();
    const languages = document.getElementById("languages").value.trim();
    const softSkills = document.getElementById("softSkills").value.trim();
    const extra = document.getElementById("extra").value.trim();

    if(!name || !email || !phone){
        alert("Please fill in all required fields (*)");
        return;
    }

    function formatList(text, separator){
        return text ? '<ul>' + text.split(separator).map(item => `<li>${item.trim()}</li>`).join('') + '</ul>' : '';
    }

    function formatSection(title, content){
        return content ? `<div class="cv-section cv-no-break"><h2 class="cv-section-title">${title}</h2>${content}</div>` : '';
    }

    let educationHTML = education.split(";").map(e => {
        const parts = e.split(" - ");
        return `<div class="cv-education-item">
            <div class="cv-education-header">
                <span>${parts[0]||""}</span>
                <span>${parts[1]||""}</span>
            </div>
            <div class="cv-education-details">${parts[2]||""}</div>
        </div>`;
    }).join("");

    let projectsHTML = projects.split(";").map(p => {
        const parts = p.split(" - ");
        return `<div class="cv-project-item">
            <div class="cv-project-header">${parts[0]||""}</div>
            <div class="cv-project-details">${parts[1]||""}</div>
        </div>`;
    }).join("");

    let skillsHTML = '';
    if(techSkills) skillsHTML += `<div class="cv-skill-category"><h3>Technical Skills</h3>${formatList(techSkills, ',')}</div>`;
    if(tools) skillsHTML += `<div class="cv-skill-category"><h3>Tools</h3>${formatList(tools, ',')}</div>`;
    if(languages) skillsHTML += `<div class="cv-skill-category"><h3>Languages</h3>${formatList(languages, ',')}</div>`;
    if(softSkills) skillsHTML += `<div class="cv-skill-category"><h3>Soft Skills</h3>${formatList(softSkills, ',')}</div>`;

    let extraHTML = extra.split(";").map(e => {
        const parts = e.split(" - ");
        return `<div class="cv-project-item">
            <div class="cv-project-header">${parts[0]||""}</div>
            <div class="cv-project-details">${parts[1]||""}</div>
        </div>`;
    }).join("");

    cvContent.innerHTML = `
        <div class="cv-header">
            <h1 class="cv-name">${name}</h1>
            <div class="cv-title">${title}</div>
            <div class="cv-contact-info">
                <span>${email}</span>
                <span>${phone}</span>
            </div>
        </div>
        ${formatSection('Education', educationHTML)}
        ${formatSection('Projects & Competitions', projectsHTML)}
        ${skillsHTML ? `<div class="cv-section cv-no-break"><h2 class="cv-section-title">Skills</h2><div class="cv-skills-container">${skillsHTML}</div></div>` : ''}
        ${formatSection('Extra-Curricular', extraHTML)}
    `;

    cvForm.style.display = "none";
    cvPreview.style.display = "block";
});

// Print CV only
printBtn.addEventListener("click", function(){
    window.print();
});

// Edit CV
editBtn.addEventListener("click", function(){
    cvPreview.style.display = "none";
    cvForm.style.display = "block";
});
