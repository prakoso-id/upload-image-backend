import express, { Request, Response } from 'express';
import multer from 'multer';
import fs from 'fs';
import path from 'path';
import standardResponse from './middleware/response';
import cors from 'cors';
import dotenv from 'dotenv';

dotenv.config(); // Load environment variables

const app = express();
const port = process.env.PORT;

app.use(express.json());

//handle cors
app.use(cors({
  origin: ['http://localhost:3000'],
  methods: ['GET', 'POST'],
  allowedHeaders: ['Content-Type', 'Authorization', 'x-file-type']
}));

// Gunakan middleware standardResponse secara global
app.use(standardResponse);

// Create directories if they don't exist
const uploadBaseDir = path.join(__dirname, '../uploads');
const directories = ['images', 'audio', 'documents'];
directories.forEach(dir => {
  const fullPath = path.join(uploadBaseDir, dir);
  if (!fs.existsSync(fullPath)) {
    fs.mkdirSync(fullPath, { recursive: true });
  }
});

// Configure multer storage
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    const fileType = req.headers['x-file-type'];
    let uploadPath = path.join(uploadBaseDir);
    
    switch (fileType) {
      case 'image':
        uploadPath = path.join(uploadPath, 'images');
        break;
      case 'audio':
        uploadPath = path.join(uploadPath, 'audio');
        break;
      case 'document':
        uploadPath = path.join(uploadPath, 'documents');
        break;
      default:
        return cb(new Error('Invalid file type'), '');
    }

    cb(null, uploadPath);
  },
  filename: (req, file, cb) => {
    // Sanitize filename to prevent directory traversal
    const sanitizedFilename = file.originalname.replace(/[^a-zA-Z0-9.-]/g, '_');
    const uniqueFilename = `${Date.now()}-${sanitizedFilename}`;
    cb(null, uniqueFilename);
  }
});

const upload = multer({ 
  storage,
  limits: {
    fileSize: 10 * 1024 * 1024 // 10MB limit
  }
});

// Serve static files from Docker volume path
const uploadPath = '/usr/src/app/uploads';
app.use('/uploads', express.static(uploadPath));

// Protect the upload route with JWT authentication
app.post('/api/uploads', upload.single('file'), (req: Request, res: Response) => {
  if (!req.file) {
    return res.standardResponse(null, 'No file uploaded.', 400);
  }
  
  // Convert the file path to URL format
  const fileUrl = `uploads/${req.file.path.split('/uploads/')[1]}`;
  const data = { 
    filename: req.file.originalname,
    url: fileUrl
  };
  
  return res.standardResponse(data, 'File uploaded successfully');
});

// Delete file endpoint
app.post('/api/delete-file', (req: Request, res: Response) => {
  const fileUrl = req.query.url as string;

  if (!fileUrl) {
    return res.standardResponse(null, 'File URL is required', 400);
  }

  try {
    // Remove 'uploads/' prefix from the URL to get the relative path
    const relativePath = fileUrl.replace('uploads/', '');
    
    // Construct the full file path in the Docker volume
    const filePath = path.join('/usr/src/app/uploads', relativePath);
    
    console.log('Attempting to delete file:', filePath);

    // Check if file exists
    if (!fs.existsSync(filePath)) {
      console.log('File not found:', filePath);
      return res.standardResponse(null, 'File not found', 404);
    }

    // Delete the file
    fs.unlinkSync(filePath);
    console.log('File deleted successfully:', filePath);
    return res.standardResponse(null, 'File deleted successfully');
  } catch (error) {
    console.error('Error deleting file:', error);
    return res.standardResponse(null, 'Error deleting file', 500);
  }
});

app.listen(port, () => {
  console.log(`Server is running on http://localhost:${port}`);
});
