# Client Manager Style Guide

## Colors
| Color             | Variable          | Hex       | Usage                  |
|-------------------|-------------------|-----------|------------------------|
| Primary           | `--primary`      | #4361ee  | Buttons, links, accents |
| Primary Dark      | `--primary-dark` | #3a56d4  | Hover states           |
| Secondary         | `--secondary`    | #3f37c9  | Secondary actions      |
| Accent            | `--accent`       | #4cc9f0  | Highlights             |
| Success           | `--success`      | #2ecc71  | Positive indicators    |
| Warning           | `--warning`      | #f39c12  | Warnings               |
| Danger            | `--danger`       | #e74c3c  | Errors, destructive    |
| Light             | `--light`        | #f8f9fa  | Backgrounds            |
| Dark              | `--dark`         | #212529  | Text                   |
| Gray              | `--gray`         | #6c757d  | Secondary text         |
| Light Gray        | `--light-gray`   | #e9ecef  | Borders, dividers      |

## Typography
- **Font Family**: System fonts (Segoe UI, Roboto, sans-serif)
- **Base Size**: 16px
- **Line Height**: 1.6

| Element       | Size    | Weight | Color       |
|---------------|---------|--------|-------------|
| Page Title    | 2.5rem  | 700    | --dark      |
| Card Title    | 1.5rem  | 600    | --dark      |
| Body Text     | 1rem    | 400    | --dark      |
| Small Text    | 0.875rem| 400    | --gray      |

## Spacing System
- **Base Unit**: 8px
- Scale: 4px, 8px, 16px, 24px, 32px, 48px, 64px, 96px, 128px

## Components
### Buttons
- `.btn`: Base button style
- `.btn-primary`: Primary action
- `.btn-outline`: Secondary action
- `.btn-danger`: Destructive action
- `.btn-icon`: Icon-only button

### Cards
- `.card`: Container for content
- `.card-header`: Header section
- `.card-title`: Card title
- `.card-body`: Main content area
- `.card-footer`: Footer content

### Forms
- `.form-group`: Wrapper for form elements
- `.form-label`: Input label
- `.form-control`: Input, select, textarea
- `.checkbox`: Checkbox container

### Alerts
- `.alert`: Base alert
- `.alert-success`: Success message
- `.alert-error`: Error message
- `.alert-info`: Informational message

## Layout Principles
1. **Whitespace**: Generous padding and margins for clean look
2. **Consistency**: Uniform spacing and element sizing
3. **Hierarchy**: Clear visual hierarchy with typography and color
4. **Responsiveness**: Mobile-first design with breakpoints at:
   - Mobile: < 768px
   - Tablet: 768px - 1024px
   - Desktop: > 1024px