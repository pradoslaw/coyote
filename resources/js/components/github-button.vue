<template>
  <div class="github-button">
    <a class="repository"
       href="https://github.com/pradoslaw/coyote"
       title="Odwiedź repozytorium Coyote"
       rel="noopener"
       target="_blank">
      <svg class="icon" viewBox="0 0 16 16" width="16" height="16">
        <path
          d="M8 .25a.75.75 0 0 1 .673.418l1.882 3.815 4.21.612a.75.75 0 0 1 .416 1.279l-3.046 2.97.719 4.192a.751.751 0 0 1-1.088.791L8 12.347l-3.766 1.98a.75.75 0 0 1-1.088-.79l.72-4.194L.818 6.374a.75.75 0 0 1 .416-1.28l4.21-.611L7.327.668A.75.75 0 0 1 8 .25Zm0 2.445L6.615 5.5a.75.75 0 0 1-.564.41l-3.097.45 2.24 2.184a.75.75 0 0 1 .216.664l-.528 3.084 2.769-1.456a.75.75 0 0 1 .698 0l2.77 1.456-.53-3.084a.75.75 0 0 1 .216-.664l2.24-2.183-3.096-.45a.75.75 0 0 1-.564-.41L8 2.694Z"></path>
      </svg>
      <span>Coyote</span>
    </a>
    <a class="stars"
       href="https://github.com/pradoslaw/coyote/stargazers"
       rel="noopener" target="_blank">
      85
    </a>
  </div>
</template>

<style lang="scss">
.github-button {
  box-sizing: content-box;
  display: inline-block;
  overflow: hidden;
  font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Helvetica, Arial, sans-serif;
  font-size: 0;
  line-height: 0;
  white-space: nowrap;

  a {
    text-decoration: none;
    outline: 0;
  }

  .repository,
  .stars {
    position: relative;
    display: inline-block;
    vertical-align: bottom;
    cursor: pointer;
    user-select: none;
    background-repeat: repeat-x;
    background-position: -1px -1px;
    background-size: 110% 110%;
    box-sizing: content-box;
    font-weight: 600;
    border: 1px solid;
    height: 16px;
    padding: 5px 10px;
    font-size: 12px;
    line-height: 16px;

    &:focus-visible {
      outline: 2px solid #0969da;
      outline-offset: -2px;
    }
  }

  .icon {
    display: inline-block;
    vertical-align: text-top;
    fill: currentColor;
    overflow: visible;
  }

  .repository {
    color: #24292f;
    background-color: #ebf0f4;
    border-color: #ccd1d5;
    border-color: rgba(31, 35, 40, .15);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg'%3e%3clinearGradient id='o' x2='0' y2='1'%3e%3cstop stop-color='%23f6f8fa'/%3e%3cstop offset='90%25' stop-color='%23ebf0f4'/%3e%3c/linearGradient%3e%3crect width='100%25' height='100%25' fill='url(%23o)'/%3e%3c/svg%3e");
    background-image: -moz-linear-gradient(top, #f6f8fa, #ebf0f4 90%);
    background-image: linear-gradient(180deg, #f6f8fa, #ebf0f4 90%);
    filter: progid:DXImageTransform.Microsoft.Gradient(startColorstr='#FFF6F8FA', endColorstr='#FFEAEFF3');
    border-radius: .25em 0 0 .25em;

    &:active {
      background-color: #e5e9ed;
      border-color: #c7cbcf;
      border-color: rgba(31, 35, 40, .15);
      background-image: none;
      filter: none;
    }

    &:hover,
    &:focus {
      background-color: #e9ebef;
      background-position: 0 -0.5em;
      border-color: #cbcdd1;
      border-color: rgba(31, 35, 40, .15);
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg'%3e%3clinearGradient id='o' x2='0' y2='1'%3e%3cstop stop-color='%23f3f4f6'/%3e%3cstop offset='90%25' stop-color='%23e9ebef'/%3e%3c/linearGradient%3e%3crect width='100%25' height='100%25' fill='url(%23o)'/%3e%3c/svg%3e");
      background-image: -moz-linear-gradient(top, #f3f4f6, #e9ebef 90%);
      background-image: linear-gradient(180deg, #f3f4f6, #e9ebef 90%);
      filter: progid:DXImageTransform.Microsoft.Gradient(startColorstr='#FFF3F4F6', endColorstr='#FFE8EAEE');
    }
  }

  .stars {
    color: #24292f;
    background-color: #fff;
    border-color: #dddedf;
    border-color: rgba(31, 35, 40, .15);
    border-left: 0;
    border-radius: 0 .25em .25em 0;

    &:hover,
    &:focus {
      color: #0969da
    }
  }
}

:root .repository,
:root .repository:hover,
:root .repository:focus {
  filter: none
}
</style>
