const eventGroups = {
  window: "Window",
  load: "Window",
  unload: "Window",
  resize: "Window",
  scroll: "Window",
  mouse: "Mouse",
  click: "Mouse",
  dblclick: "Mouse",
  wheel: "Mouse",
  contextmenu: "Mouse",
  key: "Keyboard",
  input: "Form",
  change: "Form",
  submit: "Form",
  reset: "Form",
  select: "Form",
  copy: "Clipboard",
  cut: "Clipboard",
  paste: "Clipboard",
  drag: "Drag & Drop",
  drop: "Drag & Drop",
  touch: "Touch",
  pointer: "Pointer",
  play: "Media",
  pause: "Media",
  ended: "Media",
  volume: "Media",
  animation: "Animation / Transition",
  transition: "Animation / Transition",
  focus: "Focus",
  blur: "Focus",
  dom: "DOM"
};

// Assign category based on event name
function getCategory(eventName) {
  const base = eventName.replace(/^on/, "").toLowerCase();
  for (const key in eventGroups) {
    if (base.includes(key)) return eventGroups[key];
  }
  return "Other";
}

// Gather all possible event names from window + element
function collectEvents() {
  const found = new Set();

  for (const prop in window) if (prop.startsWith("on")) found.add(prop);
  const sampleEl = document.createElement("div");
  for (const prop in sampleEl) if (prop.startsWith("on")) found.add(prop);

  return Array.from(found).sort();
}

// Describe which tags usually support the event
function tagsFor(eventName) {
  const e = eventName.replace(/^on/, "").toLowerCase();

  if (/(load|unload|resize|scroll)/.test(e))
    return "&lt;body&gt;, &lt;iframe&gt;, &lt;img&gt;, &lt;script&gt;, &lt;link&gt;";
  if (/(click|mouse|contextmenu)/.test(e))
    return "Visible interactive elements";
  if (/key/.test(e))
    return "Keyboard-accessible elements";
  if (/(input|change|submit|reset|select)/.test(e))
    return "&lt;form&gt;, &lt;input&gt;, &lt;select&gt;, &lt;textarea&gt;";
  if (/(copy|cut|paste)/.test(e))
    return "&lt;input&gt;, &lt;textarea&gt;, [contenteditable]";
  if (/(drag|drop)/.test(e))
    return "Elements with draggable behavior";
  if (/touch/.test(e))
    return "Touch-capable elements (mobile)";
  if (/pointer/.test(e))
    return "Pointer-sensitive elements";
  if (/(play|pause|ended|volume|loadeddata)/.test(e))
    return "&lt;audio&gt;, &lt;video&gt;";
  if (/(animation|transition)/.test(e))
    return "Elements with CSS animation or transition";
  if (/(focus|blur)/.test(e))
    return "&lt;input&gt;, &lt;button&gt;, &lt;a&gt;, other focusable elements";
  if (/dom/.test(e))
    return "Document or node-level elements";

  return "Any HTML element";
}

// Build a reference link for the event
function makeDocLink(eventName) {
  const base = eventName.toLowerCase().replace(/^on/, "");
  const missing = [
    "transition", "pointer", "touch", "wheel",
    "volume", "focusin", "focusout", "aux",
    "appinstalled", "cancel", "before", "message", "webkit"
  ];

  const useMDN = missing.some(x => base.includes(x));
  const url = useMDN
    ? `https://developer.mozilla.org/en-US/docs/Web/API/Element/${base}_event`
    : `https://www.w3schools.com/jsref/event_${eventName}.asp`;

  return { url, source: useMDN ? "MDN Docs" : "W3Schools" };
}

// Build a row’s data for the table
function makeRow(eventName) {
  const base = eventName.replace(/^on/, "").toLowerCase();
  const category = getCategory(eventName);

  let desc = "Fires when the event '" + base + "' occurs.";
  if (base.includes("click")) desc = "Triggered when the user clicks an element.";
  else if (/key(down|up|press)/.test(base)) desc = "Occurs when a keyboard key is pressed or released.";
  else if (base.includes("load")) desc = "Runs after a resource finishes loading.";
  else if (base.includes("error")) desc = "Happens when an error is detected while loading.";
  else if (base.includes("input")) desc = "Fires whenever input data is modified.";
  else if (base.includes("submit")) desc = "Fires when a form is submitted.";
  else if (base.includes("focus")) desc = "Activated when an element gains or loses focus.";
  else if (/(play|pause)/.test(base)) desc = "Media playback starts or stops.";
  else if (base.includes("scroll")) desc = "Fires when scrolling occurs on an element.";

  const supported = tagsFor(eventName);
  const link = makeDocLink(eventName);

  return {
    name: eventName,
    category,
    description: desc,
    tags: supported,
    doc: link.url
  };
}

// Generate a natural-language summary instead of a plain count
function updateSearchSummary(filteredEvents) {
  const summaryElement = document.getElementById("searchSummary");
  if (!summaryElement) return;

  const searchTerm = document.getElementById("search").value.trim();
  const totalVisible = filteredEvents.length;

  if (totalVisible === 0) {
    summaryElement.textContent = "No matching events found.";
    return;
  }

  // Count how many events per category
  const categoryCount = {};
  filteredEvents.forEach(ev => {
    categoryCount[ev.category] = (categoryCount[ev.category] || 0) + 1;
  });

  // Get top 2 categories
  const topCategories = Object.entries(categoryCount)
    .sort((a, b) => b[1] - a[1])
    .slice(0, 2)
    .map(([cat]) => cat);

  // Build sentence
  let msg = `Found ${totalVisible} event${totalVisible !== 1 ? "s" : ""}`;
  if (searchTerm) msg += ` matching "${searchTerm}"`;
 
  summaryElement.textContent = msg;
}

// Display the table rows
function showTable() {
  const tbody = document.querySelector("#eventsTable tbody");
  const searchValue = document.querySelector("#search").value.toLowerCase();
  const selectedCat = document.querySelector("#category").value;
  tbody.innerHTML = "";

  const all = collectEvents().map(makeRow);
  const filtered = all.filter(ev => {
    if (selectedCat && ev.category !== selectedCat) return false;
    if (searchValue &&
      !ev.name.toLowerCase().includes(searchValue) &&
      !ev.description.toLowerCase().includes(searchValue)) return false;
    return true;
  });

  // Build visible rows
  for (const ev of filtered) {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${ev.name}</td>
      <td>${ev.category}</td>
      <td>${ev.description}</td>
      <td>${ev.tags}</td>
      <td><a href="${ev.doc}" target="_blank" rel="noopener">Docs</a></td>
    `;
    tbody.appendChild(row);
  }

  // Update natural-language summary
  updateSearchSummary(filtered);
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", () => {
  const select = document.getElementById("category");
  const allRows = collectEvents().map(makeRow);
  const categories = Array.from(new Set(allRows.map(e => e.category))).sort();

  for (const c of categories) {
    const option = document.createElement("option");
    option.value = c;
    option.textContent = c;
    select.appendChild(option);
  }

  document.getElementById("search").addEventListener("input", showTable);
  select.addEventListener("change", showTable);
  showTable();
});
