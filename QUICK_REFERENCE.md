# Modern UI Quick Reference Guide

## 🎨 Color Usage

### Primary Colors (Navigation, CTA)
```css
--primary-600: #0284c7    /* Main buttons, headers */
--primary-700: #0369a1    /* Hover states, darker variant */
--primary-50: #f0f9ff     /* Light backgrounds */
```

### Accent Colors (Success, Actions)
```css
--accent-600: #16a34a     /* Success actions */
--accent-500: #22c55e     /* Highlights */
--accent-50: #f0fdf4      /* Light success bg */
```

### Semantic Colors
```css
--danger: #ef4444         /* Errors, destructive actions */
--warning: #f59e0b        /* Warnings, cautions */
--info: #06b6d4           /* Information */
```

---

## 🎯 Component Quick Reference

### Buttons
```html
<!-- Primary Button -->
<button class="modern-btn modern-btn--primary">
  <i class="fas fa-arrow-right"></i> Continue
</button>

<!-- Secondary Button -->
<button class="modern-btn modern-btn--secondary">
  Cancel
</button>

<!-- Accent Button (Success) -->
<button class="modern-btn modern-btn--accent">
  <i class="fas fa-check"></i> Confirm
</button>

<!-- Danger Button -->
<button class="modern-btn modern-btn--danger">
  <i class="fas fa-trash"></i> Delete
</button>

<!-- Small Button -->
<button class="modern-btn modern-btn--primary modern-btn--sm">
  Small
</button>

<!-- Full Width Button -->
<button class="modern-btn modern-btn--primary modern-btn--full">
  Full Width
</button>
```

### Cards
```html
<!-- Basic Card -->
<div class="modern-card">
  <h3>Card Title</h3>
  <p>Card content goes here</p>
</div>

<!-- Elevated Card -->
<div class="modern-card modern-card--elevated">
  <h3>Important Card</h3>
  <p>With more emphasis</p>
</div>
```

### Forms
```html
<!-- Text Input -->
<div>
  <label class="form-label">Email</label>
  <input type="email" class="modern-input" placeholder="Enter email">
</div>

<!-- Input with Error -->
<input type="email" class="modern-input modern-input--error" placeholder="Invalid input">
```

### Badges
```html
<span class="modern-badge modern-badge--primary">Primary</span>
<span class="modern-badge modern-badge--accent">Success</span>
<span class="modern-badge modern-badge--danger">Error</span>
<span class="modern-badge modern-badge--warning">Warning</span>
<span class="modern-badge modern-badge--info">Info</span>
```

### Alerts
```html
<!-- Success Alert -->
<div class="modern-alert modern-alert--success">
  <i class="fas fa-check-circle"></i>
  <div>Operation completed successfully!</div>
</div>

<!-- Error Alert -->
<div class="modern-alert modern-alert--error">
  <i class="fas fa-exclamation-circle"></i>
  <div>An error occurred. Please try again.</div>
</div>

<!-- Info Alert -->
<div class="modern-alert modern-alert--info">
  <i class="fas fa-info-circle"></i>
  <div>Here's some useful information.</div>
</div>
```

### Badges for Status
```html
<!-- Category Badge (Admin) -->
<span class="category-badge badge-admin">Administrative</span>

<!-- Category Badge (Academic) -->
<span class="category-badge badge-academic">Academic</span>

<!-- Category Badge (Facility) -->
<span class="category-badge badge-facility">Facility</span>
```

---

## 📱 Responsive Utilities

### Mobile-First Approach
```css
/* Mobile (default) */
.sidebar { display: none; }

/* Tablet and up */
@media (min-width: 768px) {
  .sidebar { display: block; }
}

/* Desktop and up */
@media (min-width: 1024px) {
  .sidebar { width: 280px; }
}
```

### Responsive Grid
```html
<!-- 1 column on mobile, 2 on tablet, 3 on desktop -->
<div class="office-grid">
  <!-- Items auto-fill with minmax(280px, 1fr) -->
</div>
```

---

## 🎬 Animation & Transitions

### Available Animations
```html
<!-- Slide Up -->
<div class="animate-slide-in-up">Content appears from below</div>

<!-- Slide Down -->
<div class="animate-slide-in-down">Content appears from above</div>

<!-- Fade In with Scale -->
<div class="animate-fade-in-scale">Content fades and scales in</div>

<!-- Smooth Transition -->
<div class="modern-transition">Smooth transition on any change</div>
```

### CSS Keyframes
```css
@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
```

---

## 🎯 Tailwind Classes Reference

### Common Utilities Used
```html
<!-- Spacing -->
<div class="p-4 m-2">Padding 4, Margin 2</div>

<!-- Colors -->
<div class="bg-primary-50 text-primary-700">Colored text</div>

<!-- Typography -->
<h1 class="text-2xl font-bold">Heading</h1>
<p class="text-sm text-gray-600">Small gray text</p>

<!-- Layout -->
<div class="flex gap-4 items-center">Flex layout</div>
<div class="grid grid-cols-3 gap-4">Grid layout</div>

<!-- Responsive -->
<div class="hidden md:block">Visible on tablet+</div>
<div class="w-full md:w-1/2">Full width mobile, half on tablet</div>

<!-- Shadows -->
<div class="shadow-lg">Large shadow</div>
<div class="shadow-elevation">Elevation shadow</div>

<!-- Border Radius -->
<div class="rounded-lg">Large rounded corners</div>
<div class="rounded-full">Circular</div>

<!-- Transitions -->
<button class="transition hover:bg-primary-700">Smooth hover</button>
```

---

## 📐 Spacing Scale

| Class | Value | Pixels |
|-------|-------|--------|
| p-1 | 0.25rem | 4px |
| p-2 | 0.5rem | 8px |
| p-3 | 0.75rem | 12px |
| p-4 | 1rem | 16px |
| p-6 | 1.5rem | 24px |
| p-8 | 2rem | 32px |

---

## 🎨 Typography Scale

| Class | Size | Usage |
|-------|------|-------|
| text-xs | 0.75rem | Small labels |
| text-sm | 0.875rem | Body small |
| text-base | 1rem | Body default |
| text-lg | 1.125rem | Emphasis |
| text-xl | 1.25rem | Section titles |
| text-2xl | 1.5rem | Page titles |
| text-3xl | 1.875rem | Main heading |

---

## 🔍 Font Weights

```css
font-normal    /* 400 - Body text */
font-medium    /* 500 - Slightly bold */
font-semibold  /* 600 - Medium bold */
font-bold      /* 700 - Headings */
font-extrabold /* 800 - Strong emphasis */
```

---

## 💡 Best Practices

### Do's ✅
- Use semantic HTML (button, input, form, etc.)
- Apply accessible labels and ARIA attributes
- Test on mobile devices regularly
- Use the design system colors
- Keep component spacing consistent
- Maintain proper heading hierarchy
- Optimize images and assets
- Use relative units (rem, em, %)

### Don'ts ❌
- Don't override Tailwind defaults unnecessarily
- Don't use inline styles unless required
- Don't forget mobile responsiveness
- Don't use deprecated color names
- Don't mix design systems
- Don't forget accessibility attributes
- Don't use overly complex animations
- Don't hardcode colors without reason

---

## 🧪 Testing Checklist

When modifying UI components:

- [ ] Test on mobile (375px - 480px)
- [ ] Test on tablet (768px - 1024px)
- [ ] Test on desktop (1920px+)
- [ ] Check touch targets (44x44px+)
- [ ] Verify color contrast (4.5:1 ratio)
- [ ] Test keyboard navigation (Tab key)
- [ ] Check screen reader support
- [ ] Test form validation messages
- [ ] Verify animations smoothness
- [ ] Test with slow network (3G)

---

## 🚀 Common Patterns

### Header with Search
```html
<header class="modern-header">
  <div class="header-content">
    <h1 class="header-title">CPSU Navigator</h1>
  </div>
</header>
```

### Grid Layout
```html
<div class="office-grid">
  <!-- Items automatically responsive -->
</div>
```

### Form Section
```html
<div class="form-group">
  <label class="form-label">Label</label>
  <input class="modern-input" type="text">
</div>
```

### Success State
```html
<div class="modern-alert modern-alert--success">
  <i class="fas fa-check-circle"></i>
  Success message
</div>
```

---

## 📞 Need Help?

- Check `MODERN_UI_GUIDE.md` for detailed documentation
- Review `resources/css/modern.css` for component definitions
- Look at view files for implementation examples
- Test components in browser DevTools

**Last Updated:** May 2026
