document.addEventListener("DOMContentLoaded", function () {   //This waits until the HTML page fully loads to prevent errors
    const container = document.getElementById("items-container"); //create references to HTML elements for later use
    const searchBox = document.getElementById("searchBox");
    const statusFilter = document.getElementById("statusFilter");
    const categoryFilter = document.getElementById("categoryFilter");
    const sortByDate = document.getElementById("sortByDate");

    let itemsData = [];
    let isAdmin = false;  //will be true if the backend says the user is admin after fetching 

    // ---------------- FETCH XML ITEMS ----------------
    fetch("get_items.php") //fetch the XML data from the PHP file
        .then(res => res.text())
        .then(str => {
            const parser = new DOMParser();
            const xml = parser.parseFromString(str, "text/xml");

            isAdmin = xml.getElementsByTagName("isAdmin")[0]?.textContent === "1";   //check if admin
            const items = xml.getElementsByTagName("item");

            itemsData = [];

            for (let i = 0; i < items.length; i++) {
                const get = tag => items[i].getElementsByTagName(tag)[0]?.textContent || "";

                itemsData.push({
                    id: get("id"),
                    name: get("item_name"),
                    desc: get("description"),
                    status: get("status"),
                    category: get("category"),
                    date: get("date"),
                    location: get("location"),
                    photo: get("photo"),
                    posterName: get("poster_name"),
                    posterEmail: get("poster_email"),
                    posterPhone: get("poster_phone"),
                    returned: get("returned") === "1"
                });
            }

            filterAndRender();
        })
        .catch(err => console.error("Error loading items:", err));

    // RENDER ITEMS 
    function renderItems(items) {
        container.innerHTML = "";

        items.forEach(item => {
            const card = document.createElement("div");
            card.className = "post-card";

            let badgesHTML = `
                <span class="badge" style="background:${item.status === 'Found' ? 'green' : 'red'}">
                    ${item.status}
                </span>
            `;
            if (item.returned) {
                badgesHTML += `<span class="badge returned-badge" style="background:orange">Returned</span>`;
            }

            let adminBtnHTML = "";
            if (isAdmin) {
                adminBtnHTML = `<button class="delete-btn" data-id="${item.id}">Delete</button>`;
            }

            card.innerHTML = `
                <div class="item-title">${item.name}</div>
                ${item.photo ? `<img src="${item.photo}" class="item-image">` : `<div class="no-photo">No Image</div>`}
                <div class="badges">${badgesHTML}</div>
                <p class="description">${item.desc}</p>
                ${adminBtnHTML}
            `;

            //  MODAL 
            const modal = document.createElement("div");
            modal.className = "modal";
            const formattedPhone = formatPhone(item.posterPhone);

            modal.innerHTML = `
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>${item.name}</h2>
                    ${item.photo ? `<img src="${item.photo}" class="modal-image">` : ""}
                    <p><b>Description:</b> ${item.desc}</p>
                    <p><b>Category:</b> ${item.category}</p>
                    <p><b>Location:</b> ${item.location}</p>
                    <p><b>Date:</b> ${item.date}</p>
                    <h3>Contact</h3>
                    <p><b>Name:</b> ${item.posterName}</p>
                    <p><b>Email:</b> ${item.posterEmail}</p>
                    <p><b>Phone:</b> ${formattedPhone}</p>
                </div>
            `;

            document.body.appendChild(modal);

            // Open modal
            card.addEventListener("click", () => modal.style.display = "flex");

            // Close modal
            modal.querySelector(".close").addEventListener("click", (e) => {
                e.stopPropagation();
                modal.style.display = "none";
            });

            modal.addEventListener("click", (e) => {
                if (e.target === modal) {
                    modal.style.display = "none";
                }
            });

            // Add card
            container.appendChild(card);

            // ADMIN DELETE BUTTON 
            if (isAdmin) {
                const btn = card.querySelector(".delete-btn");
                if (btn) {
                    btn.addEventListener("click", function (e) {
                        e.stopPropagation();   // to prevent modal opening
                        deleteItem(item.id);
                    });
                }
            }
        });
    }

    //  FORMAT PHONE 
    function formatPhone(phone) {
        if (!phone) return "";
        const digits = phone.replace(/\D/g, '');
        if (digits.length === 8) {
            return '+961 ' + digits.replace(/(\d{2})(\d{3})(\d{3})/, '$1 $2 $3');
        }
        return phone;
    }

    // FILTER + SORT
    function filterAndRender() {
        const search = searchBox.value.toLowerCase();
        const status = statusFilter.value;
        const category = categoryFilter.value;
        const sortOrder = sortByDate.value;

        let filtered = itemsData.filter(item => {
            const matchesSearch =
                item.name.toLowerCase().includes(search) ||
                item.desc.toLowerCase().includes(search);

            let matchesStatus = true;
            if (status === "found/not-returned") matchesStatus = item.status === "Found" && !item.returned;
            else if (status === "lost/not-returned") matchesStatus = item.status === "Lost" && !item.returned;
            else if (status === "found/returned") matchesStatus = item.status === "Found" && item.returned;
            else if (status === "lost/returned") matchesStatus = item.status === "Lost" && item.returned;

            let matchesCategory = true;
            if (category) matchesCategory = item.category === category;

            return matchesSearch && matchesStatus && matchesCategory;
        });

        if (sortOrder === "newest") {
            filtered.sort((a, b) => new Date(b.date) - new Date(a.date));
        } else if (sortOrder === "oldest") {
            filtered.sort((a, b) => new Date(a.date) - new Date(b.date));
        }

        renderItems(filtered);
    }

    //  LIVE FILTERS 
    searchBox.addEventListener("input", filterAndRender);
    statusFilter.addEventListener("change", filterAndRender);
    categoryFilter.addEventListener("change", filterAndRender);
    sortByDate.addEventListener("change", filterAndRender);
});

//  DELETE FUNCTION (JSON → PHP) 
function deleteItem(id) {
    if (!confirm("Delete this item?")) return;

    fetch("delete_item.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ item_id: id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Item deleted");
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(err => console.error("Delete error:", err));
}
