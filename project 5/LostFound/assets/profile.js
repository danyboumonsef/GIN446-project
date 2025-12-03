document.addEventListener("DOMContentLoaded", function () {
    const container = document.getElementById("profile-items-container");

    function loadItems() {
        fetch("get_user_items.php")
        .then(res => res.text())
        .then(str => {
            const parser = new DOMParser();
            const xml = parser.parseFromString(str, "text/xml");
            const items = xml.getElementsByTagName("item");

            container.innerHTML = "";

            for (let i = 0; i < items.length; i++) {

                const get = tag => items[i].getElementsByTagName(tag)[0]?.textContent || "";

                const id = get("id");
                const name = get("item_name");
                const desc = get("description");
                const status = get("status");
                const returned = get("returned") === "1";
                const photo = get("photo");

                const card = document.createElement("div");
                card.className = "post-card";

                card.innerHTML = `
                    <div class="item-title">${name}</div>
                    ${photo ? `<img src="${photo}" class="item-image">` : `<div class="no-photo">No Image</div>`}
                    <div class="badges">
                        <span class="badge" style="background:${status==='Found'?'green':'red'}">${status}</span>
                        ${returned ? `<span class="badge returned-badge" style="background:orange">Returned</span>` : ""}
                    </div>
                    <p class="description">${desc}</p>
                    <div class="item-actions">
                        <button class="toggle-return-btn">${returned ? "Unmark Returned" : "Mark Returned"}</button>
                        <button class="edit-btn">Edit</button>
                        <button class="delete-btn">Delete</button>
                    </div>
                `;

                // Toggle returned badge visually 
                const toggleBtn = card.querySelector(".toggle-return-btn");
                toggleBtn.addEventListener("click", () => {
                    fetch("toggle_returned.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ item_id: id })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const badges = card.querySelector(".badges");
                            let badge = badges.querySelector(".returned-badge");

                            if (data.returned) {
                                if (!badge) {
                                    badge = document.createElement("span");
                                    badge.className = "badge returned-badge";
                                    badge.style.background = "orange";
                                    badge.textContent = "Returned";
                                    badges.appendChild(badge);
                                }
                                toggleBtn.textContent = "Unmark Returned";
                            } else {
                                if (badge) badge.remove();
                                toggleBtn.textContent = "Mark Returned";
                            }
                        }
                    })
                    .catch(console.error);
                });

                //  Edit
                card.querySelector(".edit-btn").addEventListener("click", () => {
                    window.location.href = "edit_post.php?item_id=" + id;
                });

                // Delete 
                const deleteBtn = card.querySelector(".delete-btn");
                deleteBtn.addEventListener("click", () => {
                    if (!confirm("Delete this post permanently?")) return;

                    fetch("delete_item.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ item_id: id })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            card.remove();
                        } else {
                            console.error(data.message);
                        }
                    })
                    .catch(console.error);
                });

                container.appendChild(card);
            }
        })
        .catch(console.error);
    }

    loadItems();
});

// Phone update 
document.getElementById("updatePhoneForm").addEventListener("submit", function(e){
    e.preventDefault();

    const phone = document.getElementById("newPhoneField").value;
    fetch("update_phone.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ phone: phone })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            document.getElementById("phone-feedback").textContent = "Phone updated successfully!";
            document.getElementById("phone-feedback").style.color = "green";
			
            setTimeout(()=> location.reload(), 1000);
        } else {
            document.getElementById("phone-feedback").textContent = data.error;
            document.getElementById("phone-feedback").style.color = "red";
        }
    })
    .catch(err => {
        document.getElementById("phone-feedback").textContent = "Server error!";
        document.getElementById("phone-feedback").style.color = "red";
    });
});
    
document.addEventListener("DOMContentLoaded", function() {
    const params = new URLSearchParams(window.location.search);
    const edited = params.get("edited");

    if (edited === "1") {
        // Create popup
        const popup = document.createElement("div");
        popup.textContent = "Item edited successfully!";
        popup.style.position = "fixed";
        popup.style.top = "50%";
        popup.style.left = "50%";
        popup.style.transform = "translate(-50%, -50%)";
        popup.style.background = "rgba(0, 128, 0, 0.85)";
        popup.style.color = "#fff";
        popup.style.padding = "15px 25px";
        popup.style.borderRadius = "10px";
        popup.style.fontSize = "16px";
        popup.style.zIndex = "9999";
        document.body.appendChild(popup);

        // Remove popup after 3 seconds
        setTimeout(() => {
            popup.remove();
        }, 3000);

        // Remove `edited=1` from URL
        const url = new URL(window.location);
        url.searchParams.delete("edited");
        window.history.replaceState({}, document.title, url.toString());
    }
});