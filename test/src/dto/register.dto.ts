import { IsString, IsEmail } from "class-validator";

export class RegisterDto {
  @IsString()
  username: string;

  @IsString()
  @IsEmail()
  email: string;

  @IsString()
  password: string;

  @IsString()
  given_name: string;

  @IsString()
  family_name: string;

  @IsString()
  name: string;
}