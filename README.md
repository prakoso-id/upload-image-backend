# Image Upload and Management System

This project consists of two main services that work together to handle image uploads and manage image data:

## Project Structure

### 1. file-upload-api
This service is responsible for handling image file uploads to storage. It provides:
- File upload endpoint
- File validation and processing
- Secure storage management
- Support for various image formats

#### Endpoints
- `/api/uploads` - Upload images
- `/api/delete-file` - Delete images
- `/uploads` - View uploaded images

#### Installation & Running
You can run the service in two ways:

1. Using Docker:
```bash
docker-compose up
```

2. Using npm:
```bash
npm install
npm run build
npm run start
```

### 2. rest-api
This service manages image metadata and database operations. It provides:
- Image metadata storage in database
- CRUD operations for image data
- Image information retrieval
- Database management

#### Endpoints
- GET|HEAD   api/images   
- POST       api/images   
- GET|HEAD   api/images/{id}  
- DELETE     api/images/{id}

#### Installation & Running
You can run the service in two ways:

1. Using Docker:
```bash
docker-compose up -d --build
```

2. Using php artisan
```bash
php artisan serve
```

## Getting Started

To get started with the project, follow these steps:

1. Clone the repository
2. Install dependencies for both services using npm or Docker
3. Start both services using npm or Docker

## Features
- Secure file upload handling
- Image storage management
- Database integration for image metadata
- RESTful API endpoints

## Technologies
- Backend services
- Database system
- File storage system
