import { Request, Response, NextFunction } from 'express';

const standardResponse = (req: Request, res: Response, next: NextFunction) => {
  res.standardResponse = (data: any, message: string = 'Success', status: number = 200) => {
    res.status(status).json({
      status: status,
      message: message,
      data: data,
    });
  };
  next();
};

export default standardResponse;

// Extend Response interface to include standardResponse method
declare global {
  namespace Express {
    interface Response {
      standardResponse: (data: any, message?: string, status?: number) => void;
    }
  }
}
