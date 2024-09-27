async function apiPost(url, body) {
  try {
    const res = await fetch(url, { method: "POST", body: body });
    const resData = await res.json();
    return resData;
  } catch (error) {
    console.error("Error:", error);
    return null;
  }
}

async function resetFormValidations() {
  const invalidInputs = document.querySelectorAll("input.is-invalid");
  invalidInputs.forEach(function(input) {
    input.classList.remove("is-invalid");
  });
}

async function setFormValidation(fieldId, message) {
  const field = document.getElementById(fieldId);
  field.classList.add("is-invalid");
  const errorMsg = document.getElementById(`${fieldId}-error-msg`);
  errorMsg.innerText = message;
}

function createPostEl(post, parent) {
  const cardDiv = document.createElement("div");
  cardDiv.classList.add("card", "p-3", "rounded-0");
  cardDiv.style.cursor = "pointer";
  cardDiv.addEventListener("click", () => {
    console.log("card clicked!");
  });

  const cardContentDiv = document.createElement("div");
  cardContentDiv.classList.add("d-flex");

  /** 左ブロック */
  // プロフィール画像
  const leftDiv = document.createElement("div");

  const profileLink = document.createElement("a");
  profileLink.href = post.profilePath;

  const profileImg = document.createElement("img");
  profileImg.src = post.profileImagePath;
  profileImg.alt = "プロフィール画像";
  profileImg.width = 40;
  profileImg.height = 40;
  profileImg.classList.add("rounded-circle");

  profileLink.appendChild(profileImg);
  leftDiv.appendChild(profileLink);

  /** 右ブロック */
  const rightDiv = document.createElement("div");
  rightDiv.classList.add("w-100", "ms-2");

  // 名前, ユーザー名, 3点ドットアイコン
  const profileDiv = document.createElement("div");
  profileDiv.classList.add("d-flex", "justify-content-between");

  const userLink = document.createElement("a");
  userLink.href = post.profilePath;
  userLink.classList.add("text-black", "hover-underline");

  const nameSpan = document.createElement("span");
  nameSpan.classList.add("fw-semibold", "fs-6");
  nameSpan.innerText = post.name + " ";

  const usernameSpan = document.createElement("span");
  usernameSpan.classList.add("fw-light", "text-secondary");
  usernameSpan.innerText = `@${post.username}・${post.postedAt}`;

  userLink.appendChild(nameSpan);
  userLink.appendChild(usernameSpan);

  const dropdownDiv = document.createElement("div");
  dropdownDiv.classList.add("dropdown");
  dropdownDiv.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopPropagation();
  });

  const threeDotsIcon = document.createElement("ion-icon");
  threeDotsIcon.setAttribute("name", "ellipsis-horizontal-outline");
  threeDotsIcon.classList.add("dropdown-toggle", "rounded", "hover-action");
  threeDotsIcon.setAttribute("data-bs-toggle", "dropdown");
  threeDotsIcon.setAttribute("aria-expanded", "false");
  threeDotsIcon.style.cursor = "pointer";

  const dropdownMenu = document.createElement("ul");
  dropdownMenu.classList.add("dropdown-menu");

  const deleteItem = document.createElement("li");
  const deleteLink = document.createElement("a");
  deleteLink.classList.add("dropdown-item");
  deleteLink.href = "#";
  deleteLink.innerText = "削除";
  deleteLink.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopPropagation();
  });

  deleteItem.appendChild(deleteLink);
  dropdownMenu.appendChild(deleteItem);

  dropdownDiv.appendChild(threeDotsIcon);
  dropdownDiv.appendChild(dropdownMenu);

  profileDiv.appendChild(userLink);
  profileDiv.appendChild(dropdownDiv);

  // 本文
  const textBody = document.createElement("div");
  textBody.innerText = post.content;

  // 返信アイコン・返信数, いいねアイコン・いいね数
  const iconsDiv = document.createElement("div");
  iconsDiv.classList.add("mt-2", "d-flex", "align-items-center");

  const replyDiv = document.createElement("div");
  replyDiv.classList.add("text-dark", "text-decoration-none", "d-flex", "align-items-center", "rounded", "hover-action");
  replyDiv.style.cursor = "pointer";

  const replyIcon = document.createElement("ion-icon");
  replyIcon.setAttribute("name", "chatbubbles-outline");

  const replyCount = document.createElement("span");
  replyCount.classList.add("ms-1");
  replyCount.innerText = "10";

  replyDiv.appendChild(replyIcon);
  replyDiv.appendChild(replyCount);

  const heartDiv = document.createElement("div");
  heartDiv.classList.add("ms-3", "text-dark", "text-decoration-none", "d-flex", "align-items-center", "rounded", "hover-action");
  heartDiv.style.cursor = "pointer";

  const heartIcon = document.createElement("ion-icon");
  heartIcon.setAttribute("name", "heart-outline");

  const heartCount = document.createElement("span");
  heartCount.classList.add("ms-1");
  heartCount.innerText = "10";

  heartDiv.appendChild(heartIcon);
  heartDiv.appendChild(heartCount);

  iconsDiv.appendChild(replyDiv);
  iconsDiv.appendChild(heartDiv);

  rightDiv.appendChild(profileDiv);
  rightDiv.appendChild(textBody);
  rightDiv.appendChild(iconsDiv);

  /** 左右のブロックをカードのコンテンツブロックの子要素に追加 */
  cardContentDiv.appendChild(leftDiv);
  cardContentDiv.appendChild(rightDiv);
  cardDiv.appendChild(cardContentDiv);

  parent.appendChild(cardDiv);
}
