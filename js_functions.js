let Idvar="i0";
    function id_generator() {
      Idvar = "i" + (Number(Idvar.slice(1)) + 1);
      return Idvar;
  }
function createCalendarInput(parent_id, content = "") {
  const inputId = id_generator();
  const input_id = inputId + "calendarInput";

  const wrapper = document.createElement('div');
  wrapper.classList.add('d-flex', 'gap-2', 'align-items-center','pb-3');

  // form-floating wrapper
  const floatingWrapper = document.createElement('div');
  floatingWrapper.classList.add('form-floating', 'flex-grow-1', 'position-relative');

  const input = document.createElement("input");
  input.type = "text";
  input.className = "form-control";
  input.id = input_id;
  input.placeholder = "Étel neve"; // szükséges a lebegéshez!
  input.setAttribute("autocomplete", "off");
  input.value = content;
  input.name = "calendar_input[]";

  const label = document.createElement("label");
  label.setAttribute("for", input_id);
  label.textContent = "Étel neve";
  label.classList.add("ps-3");

  const dropdown = document.createElement("ul");
  dropdown.classList.add('dropdown-menu');
  dropdown.style.position = 'absolute';
  dropdown.style.top = '100%';
  dropdown.style.left = '0';
  dropdown.style.right = '0';
  dropdown.style.zIndex = '1000';
  dropdown.style.display = 'none';
  dropdown.style.maxHeight = '200px';
  dropdown.style.overflowY = 'auto';

  // gépelés esemény
  input.addEventListener('input', () => {
    const query = input.value.trim();
    if (query.length < 2) {
      dropdown.style.display = 'none';
      return;
    }

fetch(`recept_index.php?calendar=${encodeURIComponent(query)}`)
      .then(response => response.json())
      .then(data => {
        dropdown.innerHTML = '';
        if (data.length > 0) {
          data.forEach(item => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = "#";
            a.className = "dropdown-item";
            a.textContent = item;
            a.onclick = function (e) {
              e.preventDefault();
              input.value = item;
              dropdown.style.display = 'none';
            };
            li.appendChild(a);
            dropdown.appendChild(li);
          });
          dropdown.style.display = 'block';
        } else {
          dropdown.style.display = 'none';
        }
      });
  });

  // fókusz elvesztése esetén elrejtés
  input.addEventListener('blur', () => {
    setTimeout(() => dropdown.style.display = 'none', 200);
  });

  const deleteButton = document.createElement('button');
  deleteButton.type = 'button';
  deleteButton.innerText = 'X';
  deleteButton.classList.add('btn', 'btn-outline-danger');
  deleteButton.onclick = function () {
    wrapper.remove();
  };

  const sortButton = document.createElement('button');
  sortButton.type = 'button';
  sortButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-down-up" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.5 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L11 2.707V14.5a.5.5 0 0 0 .5.5m-7-14a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L4 13.293V1.5a.5.5 0 0 1 .5-.5"/></svg>';
  sortButton.classList.add('btn', 'btn-dark');
  wrapper.appendChild(sortButton);
  // összeszerelés
  floatingWrapper.appendChild(input);
  floatingWrapper.appendChild(label);
  floatingWrapper.appendChild(dropdown);
  wrapper.appendChild(floatingWrapper);
  wrapper.appendChild(deleteButton);

  document.getElementById(parent_id).appendChild(wrapper);
}
function createInput(parentId) {
  let inputId = id_generator();

  // Külső konténer flex sorhoz
  const wrapper = document.createElement('div');
  wrapper.classList.add('mx-auto','pb-3','pt-3','border-bottom','border-primary','d-flex', 'flex-column', 'flex-sm-row', 'gap-2', 'align-items-center');

  // Mennyiség input + label form-floating-ben
  const qtyWrapper = document.createElement('div');
  qtyWrapper.classList.add('form-floating');
  qtyWrapper.style.flex = '2';

  const qtyInput = document.createElement('input');
  qtyInput.setAttribute("name", "amount[]");
  qtyInput.type = 'number';
  qtyInput.placeholder = ' '; // fontos a lebegő labelhez
  qtyInput.id = inputId + '-qty';
  qtyInput.setAttribute('aria-label', 'Mennyiség');
  qtyInput.classList.add('form-control');

  const qtyLabel = document.createElement('label');
  qtyLabel.htmlFor = qtyInput.id;
  qtyLabel.innerText = 'Mennyiség';
  qtyLabel.style.paddingLeft = '1rem';
  qtyWrapper.appendChild(qtyInput);
  qtyWrapper.appendChild(qtyLabel);

  // Egység input + datalist + label form-floating-ben
  const unitWrapper = document.createElement('div');
  unitWrapper.classList.add('form-floating');
  unitWrapper.style.flex = '2';

  const unitInput = document.createElement('input');
  unitInput.setAttribute('name', 'unit[]');
  unitInput.setAttribute('list', inputId + '-unit-list');
  unitInput.classList.add('form-control');
  unitInput.id = inputId + '-unit';
  unitInput.placeholder = ' '; // lebegő labelhez
  unitInput.setAttribute('aria-label', 'Mértékegység');

  const unitLabel = document.createElement('label');
  unitLabel.htmlFor = unitInput.id;
  unitLabel.innerText = 'Mértékegység';
  unitLabel.classList.add("ps-2");

  unitWrapper.appendChild(unitInput);
  unitWrapper.appendChild(unitLabel);

  const dataList = document.createElement('datalist');
  dataList.id = inputId + '-unit-list';

  const units = ['g', 'dkg', 'kg', 'ml', 'dl', 'l', 'csipet', 'db', 'kávéskanál', 'teáskanál', 'evőkanál', 'csésze', 'gerezd', 'szelet'];
  units.forEach(u => {
    const option = document.createElement('option');
    option.value = u;
    dataList.appendChild(option);
  });

  // Hozzávaló név input + label form-floating-ben
  const nameWrapper = document.createElement('div');
  nameWrapper.classList.add('form-floating');
  nameWrapper.style.flex = '4';
  nameWrapper.style.position = 'relative';

  const nameInput = document.createElement('input');
  nameInput.setAttribute("name", "ingredient_name[]");
  nameInput.type = 'text';
  nameInput.classList.add('form-control', 'ing-name-ajax');
  nameInput.placeholder = ' '; // lebegő labelhez
  nameInput.id = inputId + '-name';
  nameInput.setAttribute('aria-label', 'Hozzávaló neve');

  const nameLabel = document.createElement('label');
  nameLabel.htmlFor = nameInput.id;
  nameLabel.innerText = 'Hozzávaló neve';
  nameLabel.classList.add("ps-3");

  //hozzávalók autokiegészités ajaxhoz dropdown
  const dropdown = document.createElement('ul');
  dropdown.className = 'dropdown-menu';
  dropdown.style.position = 'absolute';
  dropdown.style.top = '100%';
  dropdown.style.left = '0';
  dropdown.style.right = '0';
  dropdown.style.maxHeight = '200px';
  dropdown.style.overflowY = 'auto';
  dropdown.style.display = 'none';
  dropdown.style.zIndex = '1050';

  nameWrapper.appendChild(nameInput);
  nameWrapper.appendChild(nameLabel);
  nameWrapper.appendChild(dropdown);

  // Törlés gomb (X)
  const deleteButton = document.createElement('button');
  deleteButton.type = 'button';
  deleteButton.innerText = 'X';
  deleteButton.classList.add('xbutton','btn', 'btn-outline-danger');
  deleteButton.onclick = function () {
    wrapper.remove();
  };

  // Elemsor összeállítása
  wrapper.appendChild(qtyWrapper);
  wrapper.appendChild(unitWrapper);
  wrapper.appendChild(dataList);
  wrapper.appendChild(nameWrapper);
  wrapper.appendChild(deleteButton);

  document.getElementById(parentId).appendChild(wrapper);
}

function restore_input(recipeId) {
  document.getElementById('updateId').value = recipeId;
    document.getElementById('recipe_name_input').value = document.getElementById(`recipeName-${recipeId}`).textContent;
    document.getElementById('duration').value = document.getElementById(`duration-${recipeId}`).textContent;
    document.getElementById('calories').value = document.getElementById(`calories-${recipeId}`).textContent;
    document.getElementById('type').value = document.getElementById(`typeOfDish-${recipeId}`).textContent;
    document.getElementById('serving').value = document.getElementById(`serving-${recipeId}`).textContent;
    document.getElementById('floatingTextarea').value = document.getElementById(`instructions-${recipeId}`).textContent;
    document.getElementById('type').value = document.getElementById(`typeOfDish-${recipeId}`).textContent;

}function importIngredientsFromDOM(listId, targetContainerId) {
  const ingredientsList = document.getElementById(listId);
  const container = document.getElementById(targetContainerId);

  container.innerHTML = '';

  const listItems = ingredientsList.querySelectorAll('li');

  listItems.forEach(li => {
    const amount = li.querySelector('.amount')?.textContent.trim() || '';
    const unit = li.querySelector('.unit')?.textContent.trim() || '';
    const name = li.querySelector('.name')?.textContent.trim() || '';

    const inputId = id_generator();

    const wrapper = document.createElement('div');
    wrapper.classList.add('mx-auto','pb-3','pt-3','border-bottom','border-primary','d-flex', 'flex-column', 'flex-sm-row', 'gap-2', 'align-items-center');

    // Mennyiség input (form-floating)
    const qtyGroup = document.createElement('div');
    qtyGroup.classList.add('form-floating');
    qtyGroup.style.flex = '2';
    const qtyInput = document.createElement('input');
    qtyInput.setAttribute('name', 'amount[]');
    qtyInput.type = 'number';
    qtyInput.classList.add('form-control');
    qtyInput.id = inputId + '-qty';
    qtyInput.value = amount;
    qtyInput.placeholder = 'Mennyiség';
    const qtyLabel = document.createElement('label');
    qtyLabel.setAttribute('for', qtyInput.id);
    qtyLabel.textContent = 'Mennyiség';
    qtyGroup.appendChild(qtyInput);
    qtyGroup.appendChild(qtyLabel);

    // Egység input (form-floating)
    const unitGroup = document.createElement('div');
    unitGroup.classList.add('form-floating');
    unitGroup.style.flex = '2';
    const unitInput = document.createElement('input');
    unitInput.setAttribute('name', 'unit[]');
    unitInput.setAttribute('list', inputId + '-unit-list');
    unitInput.classList.add('form-control');
    unitInput.id = inputId + '-unit';
    unitInput.value = unit;
    unitInput.placeholder = 'Mértékegység';
    const unitLabel = document.createElement('label');
    unitLabel.setAttribute('for', unitInput.id);
    unitLabel.textContent = 'Egység';
    const dataList = document.createElement('datalist');
    dataList.id = inputId + '-unit-list';
    const units = ['g', 'dkg', 'kg', 'ml', 'dl', 'l', 'csipet', 'db', 'kávéskanál', 'teáskanál', 'evőkanál', 'csésze', 'gerezd', 'szelet'];
    units.forEach(u => {
      const option = document.createElement('option');
      option.value = u;
      dataList.appendChild(option);
    });
    unitGroup.appendChild(unitInput);
    unitGroup.appendChild(unitLabel);
    unitGroup.appendChild(dataList);

    // Hozzávaló input (form-floating)
    const nameGroup = document.createElement('div');
    nameGroup.classList.add('form-floating');
    nameGroup.style.flex = '4';
    nameGroup.style.position = 'relative';
    const nameInput = document.createElement('input');
    nameInput.setAttribute('name', 'ingredient_name[]');
    nameInput.type = 'text';
    nameInput.classList.add('form-control', 'ing-name-ajax');
    nameInput.id = inputId + '-name';
    nameInput.placeholder = 'Hozzávaló neve';
    nameInput.value = name;
    const nameLabel = document.createElement('label');
    nameLabel.setAttribute('for', nameInput.id);
    nameLabel.textContent = 'Hozzávaló neve';
    const dropdown = document.createElement('ul');
    dropdown.className = 'dropdown-menu';
    dropdown.style.position = 'absolute';
    dropdown.style.top = '100%';
    dropdown.style.left = '0';
    dropdown.style.right = '0';
    dropdown.style.maxHeight = '200px';
    dropdown.style.overflowY = 'auto';
    dropdown.style.display = 'none';
    dropdown.style.zIndex = '1050';
    nameGroup.appendChild(nameInput);
    nameGroup.appendChild(nameLabel);
    nameGroup.appendChild(dropdown);

    const deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.innerText = 'X';
    deleteButton.classList.add('xbutton','btn', 'btn-outline-danger');
    deleteButton.onclick = () => wrapper.remove();

    wrapper.appendChild(qtyGroup);
    wrapper.appendChild(unitGroup);
    wrapper.appendChild(nameGroup);
    wrapper.appendChild(deleteButton);

    container.appendChild(wrapper);
  });
}

function modify_mode_update(){
  document.getElementById("isEditMode").value="true";
  document.getElementById("uploadbtn").textContent="Módosítás";
  document.getElementById("myModalLabel").textContent="Recept szerkesztése";
}
function modify_mode_upload(){
  document.getElementById("isEditMode").value="false";
  document.getElementById("uploadbtn").textContent="Feltöltés";
  document.getElementById("myModalLabel").textContent="Új recept hozzáadása";
}

function clearInputContainer() {
    document.getElementById("floatingTextarea").value = null;
    document.getElementById("recipe_name_input").value = null;
    const container = document.getElementById('ing_parent');
    if (container) {
     // Másolat a gyerekekről, mert közben módosítjuk az eredetit
        const children = Array.from(container.children);
        for (const child of children) {
            if (child.id === 'new_ingredient' || child.id === 'instructions_input' ) continue;
            container.removeChild(child);
        }
  }
}
function autoGrow(textarea) {
  textarea.style.height = "5px";  // először visszaállítjuk, hogy csökkenni is tudjon
  textarea.style.height = (textarea.scrollHeight) + "px";  // majd beállítjuk a tartalom magasságát
}
function passCurrentDate(date) {
  const parent = document.getElementById("meal_parent");

  // Töröljük a régi tartalmat
  while (parent.firstChild) {
    parent.removeChild(parent.firstChild);
  }

  // Létrehozunk egy rejtett inputot a kiválasztott dátummal
  const selected_date = document.createElement("input");
  selected_date.type = "hidden";
  selected_date.value = date;
  selected_date.name = "selected_date";
  parent.appendChild(selected_date);

  // Frissítjük a címet
  const title = document.getElementById("calendar_input_title");
  if (title) {
    title.textContent = "Naptár " + date;
  } else {
    console.warn("Nincs ilyen ID-jű elem: calendar_input_title");
  }

  // Lekérjük az összes elemet, amelynek data-date attribútuma megegyezik a dátummal
  const recipeElements = document.querySelectorAll("[data-date='" + date + "']");
  console.log(recipeElements);

  recipeElements.forEach(el => {
    const text = el.textContent.trim();
    if (text !== "") {
      console.log(text);
      createCalendarInput("meal_parent", text);
    }
  });
}

function search_ajax_setup(){
  const searchInput = document.getElementById('searchInput');
const searchDropdown = document.getElementById('searchDropdown');

searchInput.addEventListener('input', () => {
  const query = searchInput.value.trim();
  if (query.length < 1) {  // 1 karakter után már keres
    searchDropdown.style.display = 'none';
    return;
  }

  fetch(`recept_index.php?query=${encodeURIComponent(query)}`)
    .then(response => {
      if (!response.ok) throw new Error('Hálózati hiba');
      return response.json();
    })
    .then(data => {
      searchDropdown.innerHTML = '';
      if (Array.isArray(data) && data.length > 0) {
        data.forEach(item => {
          const li = document.createElement('li');
          const a = document.createElement('a');
          a.href = '#';
          a.className = 'dropdown-item';
          a.textContent = item.name;
          a.addEventListener('click', e => {
            e.preventDefault();
            searchInput.value = item.name;
            searchDropdown.style.display = 'none';
            // opcionálisan automatikusan küldheted a formot:
            // document.getElementById('searchForm').submit();
          });
          li.appendChild(a);
          searchDropdown.appendChild(li);
        });
        searchDropdown.style.display = 'block';
      } else {
        searchDropdown.style.display = 'none';
      }
    })
    .catch(error => {
      console.error('Fetch hiba:', error);
      searchDropdown.style.display = 'none';
    });
});

// Ha az input elveszti a fókuszt, akkor kicsit késleltetve elrejthetjük a dropdown-t (hogy kattinthassunk a javaslatokra)
searchInput.addEventListener('blur', () => {
  setTimeout(() => {
    searchDropdown.style.display = 'none';
  }, 200);
});

// Ha az input elveszti a fókuszt, akkor kicsit késleltetve elrejthetjük a dropdown-t (hogy kattinthassunk a javaslatokra)
searchInput.addEventListener('blur', () => {
  setTimeout(() => {
    searchDropdown.style.display = 'none';
  }, 200);
});

}
function ingredient_ajax_setup() {
  const searchInputs = document.getElementsByClassName("ing-name-ajax");

  Array.from(searchInputs).forEach(searchInput => {
    const wrapper = searchInput.parentElement;
    const searchDropdown = wrapper.querySelector(".dropdown-menu");

    if (!searchDropdown) return;

    searchInput.addEventListener('input', () => {
      const query = searchInput.value.trim();
      if (query.length < 1) {
        searchDropdown.style.display = 'none';
        return;
      }

      fetch(`recept_index.php?ingnames=${encodeURIComponent(query)}`)
        .then(response => {
          if (!response.ok) throw new Error('Hálózati hiba');
          return response.json();
        })
        .then(data => {
          searchDropdown.innerHTML = '';
          if (Array.isArray(data) && data.length > 0) {
            data.forEach(item => {
              const li = document.createElement('li');
              const a = document.createElement('a');
              a.href = '#';
              a.className = 'dropdown-item';
              a.textContent = item;

              a.addEventListener('click', e => {
                e.preventDefault();
                searchInput.value = item; // itt searchInput, nem searchInputs
                searchDropdown.style.display = 'none';
              });

              li.appendChild(a);
              searchDropdown.appendChild(li);
            });
            searchDropdown.style.display = 'block';
          } else {
            searchDropdown.style.display = 'none';
          }
        })
        .catch(error => {
          console.error('Fetch hiba:', error);
          searchDropdown.style.display = 'none';
        });
    });

    searchInput.addEventListener('blur', () => {
      setTimeout(() => {
        searchDropdown.style.display = 'none';
      }, 200);
    });
  });
}
