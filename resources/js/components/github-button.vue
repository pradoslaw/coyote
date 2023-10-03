<template>
  <div class="github-button">
    <a :class="['repository', this.size, this.theme]"
       :href="repository"
       :title="title"
       rel="noopener"
       target="_blank">
      <svg class="icon" viewBox="0 0 16 16" width="16" height="16">
        <path
          d="M8 .25a.75.75 0 0 1 .673.418l1.882 3.815 4.21.612a.75.75 0 0 1 .416 1.279l-3.046 2.97.719 4.192a.751.751 0 0 1-1.088.791L8 12.347l-3.766 1.98a.75.75 0 0 1-1.088-.79l.72-4.194L.818 6.374a.75.75 0 0 1 .416-1.28l4.21-.611L7.327.668A.75.75 0 0 1 8 .25Zm0 2.445L6.615 5.5a.75.75 0 0 1-.564.41l-3.097.45 2.24 2.184a.75.75 0 0 1 .216.664l-.528 3.084 2.769-1.456a.75.75 0 0 1 .698 0l2.77 1.456-.53-3.084a.75.75 0 0 1 .216-.664l2.24-2.183-3.096-.45a.75.75 0 0 1-.564-.41L8 2.694Z"></path>
      </svg>
      <span>Coyote</span>
    </a>
    <a :class="['stars', this.size, this.theme]"
       href="https://github.com/pradoslaw/coyote/stargazers"
       rel="noopener" target="_blank">
      {{ stars || '?' }}
    </a>
  </div>
</template>

<script>
function oneOf(...values) {
  return value => values.indexOf(value) > -1;
}

export default {
  data: () => ({
    title: 'Odwiedź repozytorium Coyote',
    repository: 'https://github.com/pradoslaw/coyote',
    stars: null
  }),
  props: {
    size: {require: true, validator: oneOf('large', 'small')},
    theme: {require: true, validator: oneOf('light', 'dark')},
  },
  created() {
    const renderedStars = window.document.body.dataset.githubStars;
    if (renderedStars !== 'failure') {
      this.stars = renderedStars;
    } else {
      fetch('https://api.github.com/repos/pradoslaw/coyote')
        .then(response => response.json())
        .then(response => {
          this.stars = response.stargazers_count;
        });
    }
  }
};
</script>

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

    &.large {
      height: 16px;
      padding: 5px 10px;
      font-size: 12px;
      line-height: 16px;
    }

    &.small {
      height: 14px;
      font-size: 11px;
      line-height: 14px;

      &.repository {
        padding: 1px 5px 3px;

        svg {
          margin-top: 0.5px;
        }
      }

      &.stars {
        padding: 2px 5px 2px;
      }
    }

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
    border-radius: .25em 0 0 .25em;

    svg {
      margin-right: 0.35em;
    }
  }

  .stars {
    border-radius: 0 .25em .25em 0;
    border-left: 0;
  }

  a.light {
    &.repository,
    &.stars {
      color: #24292f;
      border-color: rgba(31, 35, 40, .15);
    }

    &.repository {
      background-color: #ebf0f4;
      background-image: linear-gradient(180deg, #f6f8fa, #ebf0f4 90%);

      &:hover,
      &:focus {
        background-color: #e9ebef;
        background-image: linear-gradient(180deg, #f3f4f6, #e9ebef 90%);
        background-position: 0 -0.5em;
      }

      &:active {
        background-color: #e5e9ed;
        background-image: none;
      }
    }

    &.stars {
      background-color: white;

      &:hover,
      &:focus {
        color: #0969da;
      }
    }
  }

  a.dark {
    &.repository,
    &.stars {
      color: white;
      border-color: hsl(0deg 0% 51%);
    }

    &.repository {
      background-color: hsl(0, 0%, 38%);
      background-image: linear-gradient(180deg,
        hsl(0, 0%, 42%),
        hsl(0, 0%, 32%) 90%);
      border-right-color: hsl(0, 0%, 51%);

      &:hover,
      &:focus {
        background-color: hsl(0, 0%, 40.5%);
        background-image: linear-gradient(180deg,
          hsl(0, 0%, 45%),
          hsl(0, 0%, 35%) 90%);
      }

      &:active {
        background-color: hsl(0, 0%, 35%);
        background-image: linear-gradient(180deg,
          hsl(0, 0%, 40%),
          hsl(0, 0%, 30%) 90%);
      }
    }

    &.stars {
      background-color: hsl(0deg, 0%, 44%);

      &:hover,
      &:focus {
        color: #b3d161;
      }
    }
  }
}
</style>
