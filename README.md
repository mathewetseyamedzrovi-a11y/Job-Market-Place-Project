# Job Marketplace API

A complete backend API for an informal jobs and gig marketplace in Accra, Ghana.

## 🚀 Features

- ✅ User Authentication (Register/Login/Logout)
- ✅ Job Posting & Management
- ✅ Job Applications with Quotes
- ✅ Worker Profiles with Skills & Photos
- ✅ Search & Filters (category, location, budget)
- ✅ Job Completion & Ratings
- ✅ Real-time Messaging System
- ✅ MTN MoMo Payment Integration (Sandbox)

## 📋 API Endpoints

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register new user |
| POST | `/api/login` | Login & get token |
| GET | `/api/user` | Get authenticated user |
| POST | `/api/logout` | Logout |

### Categories
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/categories` | List all job categories |

### Jobs
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/jobs` | List jobs (with filters) |
| POST | `/api/jobs` | Create job (poster only) |
| GET | `/api/jobs/{id}` | Get job details |
| PUT | `/api/jobs/{id}` | Update job |
| DELETE | `/api/jobs/{id}` | Delete job |

### Applications
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/jobs/{id}/apply` | Apply to job |
| GET | `/api/jobs/{id}/applications` | View applicants |
| PATCH | `/api/applications/{id}/status` | Update status |
| GET | `/api/my-applications` | My applications |

### Profiles
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/profiles/{id}` | View profile |
| PUT | `/api/profile` | Update profile |
| POST | `/api/profile/photo` | Upload photo |

### Completion & Ratings
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/jobs/{id}/complete` | Mark complete |
| POST | `/api/jobs/{id}/rate` | Submit rating |
| GET | `/api/users/{id}/ratings` | View ratings |

### Messages
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/jobs/{id}/messages` | View conversation |
| POST | `/api/jobs/{id}/messages` | Send message |
| GET | `/api/conversations` | List conversations |
| PATCH | `/api/messages/{id}/read` | Mark as read |

### Payments
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/jobs/{id}/pay` | Request payment |
| GET | `/api/payments/{id}` | Payment status |
| GET | `/api/my-payments` | Payment history |

## 🛠️ Tech Stack

- **Framework:** Laravel 12.x
- **Database:** MySQL/MariaDB
- **Authentication:** Laravel Sanctum (API tokens)
- **Payments:** MTN MoMo (sandbox mode)

## 📦 Installation

1. Clone the repository
```bash
git clone https://github.com/mathewetseyamedzrovi-a11y/Job-Market-Place-Project.git